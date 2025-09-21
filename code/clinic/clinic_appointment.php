<?php
session_start();
include_once '../sql/createDB.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

if (!isset($_SESSION['idLogin'])) {
    header("Location: ../homepage.php?noLogin");
    exit;
}

$idLogin = $_SESSION['idLogin'];

if ($_POST) {
    $appointmentId = $_POST['appointmentId'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $message = $_POST['message'] ?? '';
        
        $getAppointmentSql = "SELECT a.appointDate, a.appointTime, p.petName, 
                             CONCAT(o.firstName, ' ', o.lastName) AS ownerName
                             FROM petsappointment a
                             JOIN pets p ON a.petId = p.petId
                             JOIN pet_owner o ON p.petOwnerId = o.petOwnerId
                             WHERE a.appointmentId = ?";
        $getStmt = $conn->prepare($getAppointmentSql);
        $getStmt->bind_param("i", $appointmentId);
        $getStmt->execute();
        $appointmentData = $getStmt->get_result()->fetch_assoc();
        $getStmt->close();
        
        $qrData = json_encode([
            'petName' => $appointmentData['petName'],
            'ownerName' => $appointmentData['ownerName'],
            'date' => $appointmentData['appointDate'],
            'time' => $appointmentData['appointTime'],
            'status' => 'approved'
        ]);
      $petName = $appointmentData['petName'];
        $qrCode = new QrCode($qrData);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        $qrFileName = 'qr_appointment_' . $petName . '.png';
        $qrFilePath = '../qr_codes/' . $qrFileName;
        
        if (!file_exists('../qr_codes/')) {
            mkdir('../qr_codes/', 0755, true);
        }
        
        file_put_contents($qrFilePath, $result->getString());
        
        $sql = "UPDATE petsappointment SET appointment_status = 'approved', clinicMessage = ?, qr_code = ? WHERE appointmentId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $message, $qrFileName, $appointmentId);
    } elseif ($action === 'reject') {
        $reason_for_reject = $_POST['reason_for_reject'] ?? '';
        $sql = "UPDATE petsappointment SET appointment_status = 'rejected', reason_for_reject = ? WHERE appointmentId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $reason_for_reject, $appointmentId);
    }
    
    if (isset($stmt)) {
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$search = $_GET['searchField'] ?? '';
$searchLike = "%" . $search . "%";

$sql = "SELECT a.appointmentId, a.appointDate, a.appointTime, a.purpose, a.appointment_status,
               p.petName, p.species, p.breed, p.sex, p.age, p.petImage_url,
               CONCAT(o.firstName, ' ', o.lastName) AS ownerName, o.email, o.cNumber AS contactNumber,
               a.clinicMessage, a.reason_for_reject, a.qr_code
        FROM petsappointment a
        JOIN pets p ON a.petId = p.petId
        JOIN pet_owner o ON p.petOwnerId = o.petOwnerId
        WHERE a.clinicId = ?
          AND a.appointment_status = 'pending'";

if (!empty($search)) {
    $sql .= " AND (p.petName LIKE ? OR o.firstName LIKE ? OR o.lastName LIKE ?)";
    $sql .= " ORDER BY a.appointDate DESC, a.appointTime DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $idLogin, $searchLike, $searchLike, $searchLike);
} else {
    $sql .= " ORDER BY a.appointDate DESC, a.appointTime DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idLogin);
}

$stmt->execute();
$result = $stmt->get_result();
$appointments = $result->fetch_all(MYSQLI_ASSOC);

$totalAppointments = $conn->query("SELECT COUNT(*) as total FROM petsappointment WHERE clinicId = $idLogin")->fetch_assoc()['total'];
$activeAppointments = $conn->query("SELECT COUNT(*) as active FROM petsappointment WHERE clinicId = $idLogin AND appointment_status = 'approved'")->fetch_assoc()['active'];
$pendingAppointments = $conn->query("SELECT COUNT(*) as pending FROM petsappointment WHERE clinicId = $idLogin AND appointment_status = 'pending'")->fetch_assoc()['pending'];
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VetPortal</title>
  <link rel="stylesheet" href="../output.css">
  <style>
      #sideBar { transition: all 0.5s ease; }
      .nav-item { transition: all 0.3s ease; }
      .nav-item:hover { transform: translateX(4px); }
      .nav-active { background-color: oklch(62.3% 0.214 259.815); color: white; }
      .status-pending { background-color: #fef3c7; color: #d97706; }
      .status-approved { background-color: #d1fae5; color: #059669; }
      .status-rejected { background-color: #fee2e2; color: #dc2626; }
      
      .modal {
          display: none;
          position: fixed;
          z-index: 1000;
          left: 0;
          top: 0;
          width: 100%;
          height: 100%;
          background-color: rgba(0, 0, 0, 0.5);
          animation: fadeIn 0.3s ease;
      }
      
      .modal-content {
          background-color: white;
          margin: 10% auto;
          padding: 30px;
          border-radius: 12px;
          width: 90%;
          max-width: 500px;
          box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
          animation: slideIn 0.3s ease;
      }
      
      @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
      }
      
      @keyframes slideIn {
          from { transform: translateY(-50px); opacity: 0; }
          to { transform: translateY(0); opacity: 1; }
      }
      
      .modal-header {
          display: flex;
          align-items: center;
          margin-bottom: 20px;
      }
      
      .modal-icon {
          width: 24px;
          height: 24px;
          margin-right: 12px;
      }
      
      .modal-title {
          font-size: 1.5rem;
          font-weight: bold;
          color: #1f2937;
      }
      
      .modal-textarea {
          width: 100%;
          padding: 12px;
          border: 2px solid #e5e7eb;
          border-radius: 8px;
          resize: vertical;
          min-height: 120px;
          font-family: inherit;
          transition: border-color 0.2s ease;
      }
      
      .modal-textarea:focus {
          outline: none;
          border-color: #3b82f6;
          box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
      }
      
      .modal-buttons {
          display: flex;
          gap: 12px;
          margin-top: 20px;
      }
      
      .btn {
          padding: 10px 20px;
          border: none;
          border-radius: 8px;
          font-weight: 600;
          cursor: pointer;
          transition: all 0.2s ease;
          flex: 1;
      }
      
      .btn-approve {
          background-color: #10b981;
          color: white;
      }
      
      .btn-approve:hover {
          background-color: #059669;
          transform: translateY(-1px);
      }
      
      .btn-reject {
          background-color: #ef4444;
          color: white;
      }
      
      .btn-reject:hover {
          background-color: #dc2626;
          transform: translateY(-1px);
      }
      
      .btn-cancel {
          background-color: #6b7280;
          color: white;
      }
      
      .btn-cancel:hover {
          background-color: #4b5563;
      }
  </style>
</head>
<body class="min-h-screen bg-offwhite-100">

<!-- Sidebar -->
<div class="fixed w-[60%] md:w-[18%] bg-white h-full p-6 flex flex-col text-grey-100 z-50 shadow-2xl" id="sideBar" style="transform: translateX(-100%);">
  <div class="flex flex-col">
    <div class="flex justify-end">
      <img src="../media/burger-short.png" alt="nav-icon point" class="w-10 cursor-pointer" onclick="toggleSideBar()">
    </div>
    <div class="flex mt-20">
      <div class="bg-blue-500 w-12 h-12 filter-grey rounded-md"></div>
      <div class="ml-2">
        <h1 class="font-semibold text-offblack-100 text-xl">CLINIC</h1>
        <a href="admin_profile.php" class="text-grey-100 cursor-pointer hover:text-blue-500">View Profile</a>
      </div>
    </div>
  </div>
  <div class="flex flex-col gap-3 mt-20">
    <a href="dashboard.php" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer hover:text-blue-500 p-3 nav-item">
      <p class="ml-2">Dashboard</p>
    </a>
    <a href="clinic_appointment.php" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer hover:text-blue-500 p-3 nav-active">
      <p class="ml-2">Appointment</p>
    </a>
    <a href="history.php" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer hover:text-blue-500 p-3 nav-item">
      <p class="ml-2">History</p>
    </a>
  </div>
  <a href="logout.php" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer mt-20 hover:text-blue-500 p-3 nav-item">
    <p class="ml-2">Log-out</p>
  </a>
</div>

<div class="p-10 w-full h-full flex flex-col">
  <div class="flex flex-col border-b border-b-lightgrey-100">
    <div class="flex justify-between items-center">
      <img src="../media/burger-long.png" alt="nav-icon" class="w-10 cursor-pointer" onclick="toggleSideBar()">
      <button class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-600 transition-colors flex items-center gap-2">
        QR Scanner
      </button>
    </div>
    <div class="mt-10">
      <h1 class="font-bold text-offblack-100 text-4xl">Appointments Management</h1>
    </div>
    <div class="mb-5">
      <p class="text-grey-100">In this section you can view your list of appointments.</p>
    </div>
  </div>

   <!-- Dashboard Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-8">
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow transform transition-all duration-300 ease-in-out hover:-translate-y-2 hover:shadow-lg cursor-pointer">
        <div>
            <p class="text-grey-100">Total Appointments</p>
            <h2 class="text-2xl text-blue-500 font-bold"><?= $totalAppointments ?></h2>
        </div>
        <div class="w-10 h-10 flex items-center justify-center bg-blue-100 rounded-md">
            <img src="../media/calendar-solid.svg" alt="calendar-icon" class="w-8 h-8 filter-blue">
        </div>
    </div>
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow transform transition-all duration-300 ease-in-out hover:-translate-y-2 hover:shadow-lg cursor-pointer">
        <div>
            <p class="text-grey-100">Active Appointments</p>
            <h2 class="text-2xl text-green-500 font-bold"><?= $activeAppointments ?></h2>
        </div>
        <div class="w-10 h-10 flex items-center justify-center bg-green-100 rounded-md">
            <img src="../media/clock-solid.svg" alt="clock-icon" class="w-8 h-8 filter-green">
        </div>
    </div>
    <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow transform transition-all duration-300 ease-in-out hover:-translate-y-2 hover:shadow-lg cursor-pointer">
        <div>
            <p class="text-grey-100">Pending</p>
            <h2 class="text-2xl text-yellow-500 font-bold"><?= $pendingAppointments ?></h2>
        </div>
        <div class="w-10 h-10 flex items-center justify-center bg-yellow-100 rounded-md">
            <img src="../media/paw-solid.svg" alt="pet-icon" class="w-8 h-8 filter-yellow">
        </div>
    </div>
</div>


  <!-- Search  -->
  <div class="flex flex-col md:flex-row justify-between items-center mt-8">
    <div class="w-full md:w-1/3 relative z-0">
      <form method="GET" action="">
        <input type="text" name="searchField" value="<?= htmlspecialchars($_GET['searchField'] ?? '') ?>" placeholder="Search by pet name or pets owner..." class="w-full px-4 py-2 rounded-md border border-lightgrey-100 focus:outline-none focus:border-blue-500">
        <button type="submit" class="absolute inset-y-0 right-0 flex items-center pr-3">
          <img src="../media/magnifying-glass-solid.svg" alt="search" class="w-5 h-5 text-gray-400 hover:filter-blue transition">
        </button>
      </form>
    </div>

  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-10">
    <?php if (count($appointments) > 0): ?>
      <?php foreach ($appointments as $appointment): ?>
        <div class="relative bg-white p-5 rounded-xl shadow">
          <div class="absolute top-4 right-4">
            <?php if ($appointment['appointment_status'] === 'pending'): ?>
              <span class="status-pending px-3 py-1 rounded-full text-xs font-semibold">Pending</span>
            <?php elseif ($appointment['appointment_status'] === 'approved'): ?>
              <span class="status-approved px-3 py-1 rounded-full text-xs font-semibold">Approved</span>
            <?php elseif ($appointment['appointment_status'] === 'rejected'): ?>
              <span class="status-rejected px-3 py-1 rounded-full text-xs font-semibold">Rejected</span>
            <?php endif; ?>
          </div>

          <div class="flex gap-4 mb-4">
            <img src="<?= htmlspecialchars($appointment['petImage_url'] ?? '../media/default-pet.png') ?>" 
                 alt="Pet Image" class="w-20 h-20 rounded-md object-cover flex-shrink-0">
            <div class="flex-1 min-w-0">
              <h2 class="font-bold text-lg text-gray-800 mb-2 pr-20"><?= htmlspecialchars($appointment['petName']) ?></h2>
              
              <div class="space-y-1 text-sm text-gray-600">
                <?php if (!empty($appointment['species'])): ?>
                  <p><span class="font-semibold">Species:</span> <?= htmlspecialchars($appointment['species']) ?></p>
                <?php endif; ?>
                <?php if (!empty($appointment['breed'])): ?>
                  <p><span class="font-semibold">Breed:</span> <?= htmlspecialchars($appointment['breed']) ?></p>
                <?php endif; ?>
                <?php if (!empty($appointment['sex'])): ?>
                  <p><span class="font-semibold">Sex:</span> <?= htmlspecialchars($appointment['sex']) ?></p>
                <?php endif; ?>
                <?php if (!empty($appointment['age'])): ?>
                  <p><span class="font-semibold">Age:</span> <?= htmlspecialchars($appointment['age']) ?></p>
                <?php endif; ?>
                <p><span class="font-semibold">Purpose:</span> <?= htmlspecialchars($appointment['purpose']) ?></p>
                <p><span class="font-semibold">Date & Time:</span> 
                  <?= date('M j, Y', strtotime($appointment['appointDate'])) ?> at <?= date('g:i A', strtotime($appointment['appointTime'])) ?>
                </p>
                <p><span class="font-semibold">Pet Owner:</span> <?= htmlspecialchars($appointment['ownerName']) ?> (<?= htmlspecialchars($appointment['contactNumber']) ?>)</p>
              </div>
            </div>
          </div>

          <?php if (!empty($appointment['clinicMessage'])): ?>
            <div class="mb-4 p-3 bg-green-50 border-l-4 border-green-400 rounded">
              <p class="text-sm font-semibold text-green-800 mb-1">Clinic Message:</p>
              <p class="text-sm text-green-700"><?= htmlspecialchars($appointment['clinicMessage']) ?></p>
            </div>
          <?php endif; ?>

          <?php if (!empty($appointment['reason_for_reject'])): ?>
            <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-400 rounded">
              <p class="text-sm font-semibold text-red-800 mb-1">Rejection Reason:</p>
              <p class="text-sm text-red-700"><?= htmlspecialchars($appointment['reason_for_reject']) ?></p>
            </div>
          <?php endif; ?>

          <?php if ($appointment['appointment_status'] === 'approved' && !empty($appointment['qr_code'])): ?>
            <div class="mb-4 p-3 bg-blue-50 border-l-4 border-blue-400 rounded">
              <p class="text-sm font-semibold text-blue-800 mb-2">Appointment QR Code:</p>
              <div class="flex items-center gap-3">
                <img src="../qr_codes/<?= htmlspecialchars($appointment['qr_code']) ?>" 
                     alt="Appointment QR Code" class="w-16 h-16 border rounded">
                <div class="text-xs text-blue-700">
                  <p>Show this QR code at the clinic</p>
                  <a href="../qr_codes/<?= htmlspecialchars($appointment['qr_code']) ?>" 
                     download class="text-blue-600 hover:underline">Download QR Code</a>
                </div>
              </div>
            </div>
          <?php endif; ?>

          <?php if ($appointment['appointment_status'] === 'pending'): ?>
            <div class="flex justify-end gap-3">
              <button onclick="openApproveModal(<?= $appointment['appointmentId'] ?>)" 
                      class="flex items-center gap-1 bg-green-500 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-green-600 transition-all duration-200 hover:shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                Approve
              </button>
              <button onclick="openRejectModal(<?= $appointment['appointmentId'] ?>)" 
                      class="flex items-center gap-1 bg-red-500 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-red-600 transition-all duration-200 hover:shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Reject
              </button>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
     <div class="text-center py-10 col-span-2">
        <div class="text-6xl text-grey-100 mb-4">🐾</div>
        <h3 class="text-xl font-semibold text-offblack-100 mb-2">No appointments found</h3>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Approve  -->
<div id="approveModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="modal-icon text-green-600">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
      </svg>
      <h3 class="modal-title">Approve Appointment</h3>
    </div>
    <form id="approveForm" method="POST">
      <input type="hidden" name="appointmentId" id="approveAppointmentId">
      <input type="hidden" name="action" value="approve">
      
      <label for="approveMessage" class="block text-sm font-semibold text-gray-700 mb-2">
        Message to Pet Owner (Optional)
      </label>
      <textarea 
        name="message" 
        id="approveMessage" 
        class="modal-textarea"
        placeholder="Enter any additional message or instructions for the pet owner..."
      ></textarea>
      
      <div class="modal-buttons">
        <button type="button" onclick="closeModal('approveModal')" class="btn btn-cancel">
          Cancel
        </button>
        <button type="submit" class="btn btn-approve">
          Approve Appointment
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Reject  -->
<div id="rejectModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="modal-icon text-red-600">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
      <h3 class="modal-title">Reject Appointment</h3>
    </div>
    <form id="rejectForm" method="POST">
      <input type="hidden" name="appointmentId" id="rejectAppointmentId">
      <input type="hidden" name="action" value="reject">
      
      <label for="rejectReason" class="block text-sm font-semibold text-gray-700 mb-2">
        Reason for Rejection <span class="text-red-500">*</span>
      </label>
      <textarea 
        name="reason_for_reject" 
        id="rejectReason" 
        class="modal-textarea"
        placeholder="Please provide a reason for rejecting this appointment..."
        required
      ></textarea>
      
      <div class="modal-buttons">
        <button type="button" onclick="closeModal('rejectModal')" class="btn btn-cancel">
          Cancel
        </button>
        <button type="submit" class="btn btn-reject">
          Reject Appointment
        </button>
      </div>
    </form>
  </div>
</div>

<script>
const sideBar = document.querySelector('#sideBar');

function toggleSideBar() {
  sideBar.style.transform =
    (sideBar.style.transform === 'translateX(-100%)' || sideBar.style.transform === '')
      ? 'translateX(0%)'
      : 'translateX(-100%)';
}

function openApproveModal(appointmentId) {
  document.getElementById('approveAppointmentId').value = appointmentId;
  document.getElementById('approveMessage').value = '';
  document.getElementById('approveModal').style.display = 'block';
  document.body.style.overflow = 'hidden';
}

function openRejectModal(appointmentId) {
  document.getElementById('rejectAppointmentId').value = appointmentId;
  document.getElementById('rejectReason').value = '';
  document.getElementById('rejectModal').style.display = 'block';
  document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = 'none';
  document.body.style.overflow = 'auto';
}

window.onclick = function(event) {
  const approveModal = document.getElementById('approveModal');
  const rejectModal = document.getElementById('rejectModal');
  
  if (event.target === approveModal) {
    closeModal('approveModal');
  }
  if (event.target === rejectModal) {
    closeModal('rejectModal');
  }
}

document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeModal('approveModal');
    closeModal('rejectModal');
  }
});
</script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>