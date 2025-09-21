<?php
session_start();
require_once("../sql/createDB.php");

if (!isset($_SESSION['idLogin'])) {
    header("Location: ../homepage.php?noLogin");
    exit;
}

$idLogin = $_SESSION['idLogin'];

$searchPetName = $_GET['searchField'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$clinicFilter = $_GET['clinic'] ?? '';

$whereConditions = ["pa.petOwnerId = ?"];
$params = [$idLogin];
$paramTypes = "i";

if (!empty($searchPetName)) {
    $whereConditions[] = "p.petName LIKE ?";
    $params[] = "%{$searchPetName}%";
    $paramTypes .= "s";
}

if (!empty($statusFilter) && $statusFilter !== 'All Status') {
    if ($statusFilter === 'Completed') {
        $whereConditions[] = "pa.appointment_status = 'approved' AND CONCAT(pa.appointDate, ' ', pa.appointTime) < NOW()";
    } elseif ($statusFilter === 'Rejected') {
        $whereConditions[] = "pa.appointment_status = 'rejected' AND CONCAT(pa.appointDate, ' ', pa.appointTime) < NOW()";
    } elseif ($statusFilter === 'Expired') {
        $whereConditions[] = "pa.appointment_status = 'pending' AND CONCAT(pa.appointDate, ' ', pa.appointTime) < NOW()";
    }
} else {
    $whereConditions[] = "((pa.appointment_status = 'approved' AND CONCAT(pa.appointDate, ' ', pa.appointTime) < NOW()) OR 
                          (pa.appointment_status = 'rejected' AND CONCAT(pa.appointDate, ' ', pa.appointTime) < NOW()) OR 
                          (pa.appointment_status = 'pending' AND CONCAT(pa.appointDate, ' ', pa.appointTime) < NOW()))";
}

if (!empty($dateFilter)) {
    $whereConditions[] = "pa.appointDate = ?";
    $params[] = $dateFilter;
    $paramTypes .= "s";
}

if (!empty($clinicFilter) && $clinicFilter !== 'All Clinics') {
    $whereConditions[] = "c.clinicName = ?";
    $params[] = $clinicFilter;
    $paramTypes .= "s";
}

$query = "
    SELECT 
        pa.appointmentId, pa.appointDate, pa.appointTime, pa.purpose,
        pa.appointment_status, pa.reason_for_reject, pa.qr_code,
        p.petName, p.species, p.breed, p.sex, p.age, p.petImage_url,
        c.clinicName,
        CASE 
            WHEN pa.appointment_status = 'pending' AND CONCAT(pa.appointDate, ' ', pa.appointTime) < NOW() THEN 'expired'
            WHEN pa.appointment_status = 'approved' AND CONCAT(pa.appointDate, ' ', pa.appointTime) < NOW() THEN 'completed'
            WHEN pa.appointment_status = 'rejected' AND CONCAT(pa.appointDate, ' ', pa.appointTime) < NOW() THEN 'rejected'
            ELSE pa.appointment_status
        END as display_status
    FROM petsappointment pa
    INNER JOIN pets p ON pa.petId = p.petId
    INNER JOIN clinic c ON pa.clinicId = c.clinicId
    WHERE " . implode(" AND ", $whereConditions) . "
    ORDER BY pa.appointDate DESC, pa.appointTime DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($paramTypes, ...$params);
$stmt->execute();
$appointments = $stmt->get_result();

$appointmentsArray = [];
while ($row = $appointments->fetch_assoc()) {
    $appointmentsArray[] = $row;
}

$clinicQuery = "
    SELECT DISTINCT c.clinicName 
    FROM petsappointment pa
    INNER JOIN clinic c ON pa.clinicId = c.clinicId
    WHERE pa.petOwnerId = ?
    ORDER BY c.clinicName";
$clinicStmt = $conn->prepare($clinicQuery);
$clinicStmt->bind_param("i", $idLogin);
$clinicStmt->execute();
$clinicResult = $clinicStmt->get_result();
$clinics = [];
while ($row = $clinicResult->fetch_assoc()) {
    $clinics[] = $row['clinicName'];
}

$totalHistoryQuery = "
    SELECT COUNT(*) as total 
    FROM petsappointment pa 
    WHERE pa.petOwnerId = ? AND (
        (pa.appointment_status = 'approved' AND CONCAT(pa.appointDate, ' ', pa.appointTime) < NOW()) OR 
        (pa.appointment_status = 'rejected' AND CONCAT(pa.appointDate, ' ', pa.appointTime) < NOW()) OR 
        (pa.appointment_status = 'pending' AND CONCAT(pa.appointDate, ' ', pa.appointTime) < NOW())
    )";
$totalStmt = $conn->prepare($totalHistoryQuery);
$totalStmt->bind_param("i", $idLogin);
$totalStmt->execute();
$totalHistory = $totalStmt->get_result()->fetch_assoc()['total'];

$completedQuery = "
    SELECT COUNT(*) as completed 
    FROM petsappointment pa 
    WHERE pa.petOwnerId = ? AND pa.appointment_status = 'approved' AND CONCAT(pa.appointDate, ' ', pa.appointTime) < NOW()";
$completedStmt = $conn->prepare($completedQuery);
$completedStmt->bind_param("i", $idLogin);
$completedStmt->execute();
$completedCount = $completedStmt->get_result()->fetch_assoc()['completed'];

$expiredQuery = "
    SELECT COUNT(*) as expired 
    FROM petsappointment pa 
    WHERE pa.petOwnerId = ? AND pa.appointment_status = 'pending' AND CONCAT(pa.appointDate, ' ', pa.appointTime) < NOW()";
$expiredStmt = $conn->prepare($expiredQuery);
$expiredStmt->bind_param("i", $idLogin);
$expiredStmt->execute();
$expiredCount = $expiredStmt->get_result()->fetch_assoc()['expired'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VetPortal - Appointment History</title>
  <link rel="stylesheet" href="../output.css">
 <style>
      #sideBar { transition: all 0.5s ease; }
      .nav-item { transition: all 0.3s ease; }
      .nav-item:hover { transform: translateX(4px); }
      .nav-active { background-color: oklch(62.3% 0.214 259.815); color: white; }
      .pet-card { transition: opacity 0.3s ease, transform 0.3s ease; }
      .pet-card.hidden { opacity: 0; transform: scale(0.95); display: none; }
      .status-completed { background-color: #d1fae5; color: #059669; }
      .status-rejected { background-color: #fee2e2; color: #dc2626; }
      .status-expired { background-color: #f3f4f6; color: #6b7280; }
      .filter-active { background-color: #3b82f6; color: white; }
      .history { background-color: oklch(62.3% 0.214 259.815); color: white;}
  </style>
  </head>
  <body class="min-h-screen bg-offwhite-100">
  <!-- Sidebar -->
  <?php
    include './nav.php';
  ?>

<div class="p-10 w-full h-full flex flex-col">
  <div class="flex flex-col border-b border-b-lightgrey-100">
    <div>
      <img src="../media/burger-long.png" alt="nav-icon" class="w-10 cursor-pointer" onclick="toggleSideBar()">
    </div>
    <div class="mt-10">
        <h1 class="font-bold text-offblack-100 text-4xl">Appointment History</h1>
    </div>
    <div class="mb-5">
        <p class="text-grey-100">View all completed, past rejected, and expired appointments.</p>
    </div>
  </div>

 <!-- Dashboard Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-8">
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow transform transition-all duration-300 ease-in-out hover:-translate-y-2 hover:shadow-lg cursor-pointer">
        <div>
            <p class="text-grey-100">Total History</p>
            <h2 class="text-2xl text-blue-500 font-bold"><?= $totalHistory ?></h2>
        </div>
        <div class="w-10 h-10 flex items-center justify-center bg-blue-100 rounded-md">
            <img src="../media/calendar-solid.svg" alt="calendar-icon" class="w-8 h-8 filter-blue">
        </div>
    </div>
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow transform transition-all duration-300 ease-in-out hover:-translate-y-2 hover:shadow-lg cursor-pointer">
        <div>
            <p class="text-grey-100">Completed</p>
            <h2 class="text-2xl text-green-500 font-bold"><?= $completedCount ?></h2>
        </div>
        <div class="w-10 h-10 flex items-center justify-center bg-green-100 rounded-md">
            <img src="../media/clock-solid.svg" alt="clock-icon" class="w-8 h-8 filter-green">
        </div>
    </div>
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow transform transition-all duration-300 ease-in-out hover:-translate-y-2 hover:shadow-lg cursor-pointer">
        <div>
            <p class="text-grey-100">Expired</p>
            <h2 class="text-2xl text-gray-500 font-bold"><?= $expiredCount ?></h2>
        </div>
        <div class="w-10 h-10 flex items-center justify-center bg-gray-100 rounded-md">
            <img src="../media/paw-solid.svg" alt="pet-icon" class="w-8 h-8 filter-grey">
        </div>
    </div>
</div>

<!-- Search and filters -->
<form method="GET" action="">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-8">
    <div class="w-full md:w-1/3 relative">
      <input type="text" name="searchField" value="<?= htmlspecialchars($searchPetName) ?>"
        placeholder="Search Pets"
        class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
      <span class="absolute inset-y-0 right-0 flex items-center pr-3">
        <img src="../media/magnifying-glass-solid.svg" alt="search" class="w-5 h-5 text-gray-400" />
      </span>
    </div>

    <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
      
      <div>
        <label class="block text-xs text-gray-500 mb-1">Status</label>
        <select name="status"
          class="w-full md:w-auto border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          onchange="this.form.submit()">
          <option value="">All Status</option>
          <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
          <option value="Approved" <?= $statusFilter === 'Approved' ? 'selected' : '' ?>>Approved</option>
          <option value="Completed" <?= $statusFilter === 'Completed' ? 'selected' : '' ?>>Completed</option>
          <option value="Rejected" <?= $statusFilter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
          <option value="Expired" <?= $statusFilter === 'Expired' ? 'selected' : '' ?>>Expired</option>
        </select>
      </div>

      <div>
        <label class="block text-xs text-gray-500 mb-1">Date</label>
        <div class="relative">
          <input type="date" name="date" value="<?= htmlspecialchars($dateFilter) ?>"
            class="w-full px-4 py-2 border border-lightgrey-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-offblack-100 text-sm"
            onchange="this.form.submit()">
        </div>
      </div>

      <div>
        <label class="block text-xs text-gray-500 mb-1">Clinic</label>
        <select name="clinic"
          class="w-full md:w-auto border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
          onchange="this.form.submit()">
          <option value="">All Clinics</option>
          <?php foreach ($clinics as $clinic): ?>
            <option value="<?= htmlspecialchars($clinic) ?>" <?= $clinicFilter === $clinic ? 'selected' : '' ?>>
              <?= htmlspecialchars($clinic) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <?php if (!empty($searchPetName) || !empty($statusFilter) || !empty($dateFilter) || !empty($clinicFilter)): ?>
      <div>
        <a href="history.php"
          class="inline-block px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition text-sm">
          Clear Filters
        </a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</form>


<?php if (!empty($searchPetName) || !empty($statusFilter) || !empty($dateFilter) || !empty($clinicFilter)): ?>
<div class="flex flex-wrap gap-2 mt-4">
  <span class="text-sm text-gray-600">Active filters:</span>
  
  <?php if (!empty($searchPetName)): ?>
  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
    Pet: "<?= htmlspecialchars($searchPetName) ?>"
    <a href="?<?= http_build_query(array_merge($_GET, ['searchField' => ''])) ?>" class="ml-2 text-blue-600 hover:text-blue-800">×</a>
  </span>
  <?php endif; ?>
  
  <?php if (!empty($statusFilter)): ?>
  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
    Status: <?= htmlspecialchars($statusFilter) ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['status' => ''])) ?>" class="ml-2 text-green-600 hover:text-green-800">×</a>
  </span>
  <?php endif; ?>
  
  <?php if (!empty($dateFilter)): ?>
  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
    Date: <?= date('M j, Y', strtotime($dateFilter)) ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['date' => ''])) ?>" class="ml-2 text-yellow-600 hover:text-yellow-800">×</a>
  </span>
  <?php endif; ?>
  
  <?php if (!empty($clinicFilter)): ?>
  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
    Clinic: <?= htmlspecialchars($clinicFilter) ?>
    <a href="?<?= http_build_query(array_merge($_GET, ['clinic' => ''])) ?>" class="ml-2 text-purple-600 hover:text-purple-800">×</a>
  </span>
  <?php endif; ?>
</div>
<?php endif; ?>
  
  <!-- Appointments List -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-10">
<?php if (count($appointmentsArray) > 0): ?>
    <?php foreach ($appointmentsArray as $appointment): ?>
        <div class="appointment-card relative bg-white p-5 rounded-xl shadow justify-between items-start">
            <a href="pet_list.php" class="absolute top-4 right-4 flex items-center gap-1 text-blue-500 hover:underline">
                <img src="../media/eye-solid.svg" alt="View Report" class="w-5 h-5 filter-blue">
                <span class="text-sm">View Report</span>
            </a>
            <div class="flex gap-4">
                <?php if (!empty($appointment['petImage_url'])): ?>
                    <img src="<?= htmlspecialchars($appointment['petImage_url']) ?>" alt="Pet Image" class="w-20 h-20 rounded-md object-cover">
                <?php else: ?>
                    <div class="w-20 h-20 rounded-md bg-gray-200 flex items-center justify-center">
                        <img src="../media/paw-solid.svg" alt="Default Pet" class="w-8 h-8 filter-grey">
                    </div>
                <?php endif; ?>
                <div class="flex-1">
                    <h2 class="font-bold text-lg text-gray-800"><?= htmlspecialchars($appointment['petName']) ?></h2>
                    <p class="text-sm text-gray-500">
                        <span class="font-semibold text-gray-700">Clinic:</span> <?= htmlspecialchars($appointment['clinicName']) ?>
                    </p>
                    <?php if (!empty($appointment['species'])): ?>
                        <p class="text-sm text-gray-500">
                            <span class="font-semibold text-gray-700">Species:</span> <?= htmlspecialchars($appointment['species']) ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($appointment['breed'])): ?>
                        <p class="text-sm text-gray-500">
                            <span class="font-semibold text-gray-700">Breed:</span> <?= htmlspecialchars($appointment['breed']) ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($appointment['sex'])): ?>
                        <p class="text-sm text-gray-500">
                            <span class="font-semibold text-gray-700">Sex:</span> <?= htmlspecialchars($appointment['sex']) ?>
                        </p>
                    <?php endif; ?>
                    <?php if (!empty($appointment['age'])): ?>
                        <p class="text-sm text-gray-500">
                            <span class="font-semibold text-gray-700">Age:</span> <?= htmlspecialchars($appointment['age']) ?> years
                        </p>
                    <?php endif; ?>
                    <p class="text-sm text-gray-500">
                        <span class="font-semibold text-gray-700">Purpose:</span> <?= htmlspecialchars($appointment['purpose']) ?>
                    </p>
                    <p class="text-sm text-gray-500">
                        <span class="font-semibold text-gray-700">Date & Time:</span> 
                        <?= date('M j, Y', strtotime($appointment['appointDate'])) ?> at <?= date('g:i A', strtotime($appointment['appointTime'])) ?>
                    </p>
                    <?php if ($appointment['appointment_status'] == 'rejected' && !empty($appointment['reason_for_reject'])): ?>
                        <p class="text-sm text-red-600 mt-2">
                            <span class="font-semibold">Rejection Reason:</span> <?= htmlspecialchars($appointment['reason_for_reject']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <?php
                $statusClass = '';
                $statusText = '';
                
                switch($appointment['display_status']) {
                    case 'completed':
                        $statusClass = 'status-completed';
                        $statusText = 'Completed';
                        break;
                    case 'rejected':
                        $statusClass = 'status-rejected';
                        $statusText = 'Rejected';
                        break;
                    case 'expired':
                        $statusClass = 'status-expired';
                        $statusText = 'Expired';
                        break;
                    default:
                        $statusClass = 'status-expired';
                        $statusText = 'Past';
                }
                ?>
                <div class="<?= $statusClass ?> text-xs font-semibold px-4 py-1 rounded-full">
                    <?= $statusText ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="text-center py-10 col-span-2">
        <h3 class="text-xl font-semibold text-offblack-100 mb-2">No appointment history</h3>
        <p class="text-grey-100 mb-4">
            <?php if (!empty($searchPetName) || !empty($statusFilter) || !empty($dateFilter) || !empty($clinicFilter)): ?>
                Try adjusting your filters or <a href="history.php" class="text-blue-500 hover:underline">clear all filters</a>.
            <?php else: ?>
                You don't have any completed appointments yet. <a href="./appointment_list.php" class="text-blue-500 hover:underline">View upcoming appointments</a>.
            <?php endif; ?>
        </p>
    </div>
<?php endif; ?>
</div>
</div>

  <script>
    const sideBar = document.querySelector('#sideBar');

    function toggleSideBar() {
        sideBar.style.transform = (sideBar.style.transform === 'translateX(-100%)' || sideBar.style.transform === '') ? 'translateX(0%)' : 'translateX(-100%)';
    }

  </script>
</body>
</html>