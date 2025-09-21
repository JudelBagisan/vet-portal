<?php include_once '../sql/createDB.php'; 
$clinic = null;
$schedule = [];

$stmt = $conn->prepare("SELECT * FROM clinic ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $clinic = $result->fetch_assoc();
}
$stmt->close();

if ($clinic) {
    $scheduleStmt = $conn->prepare("SELECT * FROM clinic_schedule WHERE clinicId = ? ORDER BY FIELD(dayOfWeek, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')");
    $scheduleStmt->bind_param("i", $clinic['clinicId']);
    $scheduleStmt->execute();
    $scheduleResult = $scheduleStmt->get_result();
    while ($row = $scheduleResult->fetch_assoc()) {
        $schedule[] = $row;
    }
    $scheduleStmt->close();
}

$conn->close();
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
      
      .service-highlight {
        display: inline-block;
        padding: 8px 16px;
        margin: 4px 8px 4px 0;
        border-radius: 20px;
        font-weight: 500;
        font-size: 14px;
        white-space: nowrap;
        transition: all 0.3s ease;
      }
      
      .service-highlight:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      }
      
      .general-checkups {
        background-color: #E3F2FD;
        color: #1565C0;
        border: 1px solid #BBDEFB;
      }
      
      .vaccinations {
        background-color: #E8F5E8;
        color: #2E7D32;
        border: 1px solid #C8E6C9;
      }
      
      .surgery {
        background-color: #F3E5F5;
        color: #7B1FA2;
        border: 1px solid #E1BEE7;
      }
      
      .emergency-care {
        background-color: #FFEBEE;
        color: #C62828;
        border: 1px solid #FFCDD2;
      }
      
      .dental-care {
        background-color: #FFF3E0;
        color: #EF6C00;
        border: 1px solid #FFCC02;
      }
      
      .grooming {
        background-color: #FCE4EC;
        color: #C2185B;
        border: 1px solid #F8BBD9;
      }
      
      .services-container {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        margin-top: 16px;
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
        <a href="admin_profile.php" class="text-grey-100 cursor-pointer hover:text-blue-500">View Profile</a>      </div>  
        </div>  
      </div>
      <div class="flex flex-col gap-3 mt-20">
        <a href="dashboard.html" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer hover:text-blue-500 p-3 nav-item">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
        </svg>
          <p class="ml-2">Dashboard</p>
        </a>
        <a href="clinic_appointment.php" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer hover:text-blue-500 p-3 nav-item">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
          </svg>
          <p class="ml-2">Appointment</p>
        </a>
        <a href="history.php" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer hover:text-blue-500 p-3 nav-item">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
          <p class="ml-2">History</p>
        </a>
      </div>
      <a href="logout.php" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer mt-20 hover:text-blue-500 p-3 nav-item">
        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
        </svg>
        <p class="ml-2">Log-out</p>
      </a>
    </div>
  </div>

  <!-- Main Content -->
  <main class="p-10 w-full h-full flex flex-col">
    <div class="flex flex-col border-b border-b-lightgrey-100">
      <div>
        <img src="../media/burger-long.png" alt="nav-icon" class="w-10 cursor-pointer" onclick="toggleSideBar()">
      </div>
      <div class="mt-10">
        <h1 class="font-bold text-offblack-100 text-4xl">Clinic Information</h1>
      </div>
      <div class="mb-5">
        <p class="text-grey-100 flex items-center">
          <?php if ($clinic): ?>
            Displaying information for the most recently added clinic
          <?php else: ?>
            No clinic data found. Please add a clinic first.
          <?php endif; ?>
        </p>
      </div>
    </div>

    <?php if ($clinic): ?>
    <!-- Basic Information -->
    <div class="flex flex-col items-center mt-5">
      <div class="flex flex-col p-5 bg-white w-full rounded-lg shadow-sm">
        <h1 class="font-semibold text-offblack-100 text-xl">Basic Information</h1>
        <div class="grid grid-cols-2 gap-x-5 gap-y-2 mt-5">
          <div class="p-6">
            <div class="grid md:grid-cols-2 gap-8">
              <div>
                <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">Clinic Image</h3>
                <?php if (!empty($clinic['clinicPhoto']) && file_exists($clinic['clinicPhoto'])): ?>
                  <img src="<?php echo htmlspecialchars($clinic['clinicPhoto']); ?>" alt="Clinic Photo" class="rounded-md w-full max-w-md h-auto">
                <?php else: ?>
                  <div class="bg-gray-200 rounded-md w-full max-w-md h-48 flex items-center justify-center">
                    <span class="text-gray-500">No image available</span>
                  </div>
                <?php endif; ?>
              </div>
              
              <!-- Clinic Details -->
              <div class="space-y-6">
                <div>
                  <label class="text-sm font-medium text-gray-700 mb-2">Email Address</label>
                  <p class="text-gray-900 bg-gray-50 p-3 rounded-lg"><?php echo htmlspecialchars($clinic['email']); ?></p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-700 mb-2">Clinic Name</label>
                  <p class="text-gray-900 bg-gray-50 p-3 rounded-lg"><?php echo htmlspecialchars($clinic['clinicName']); ?></p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-700 mb-2">Clinic Owner</label>
                  <p class="text-gray-900 bg-gray-50 p-3 rounded-lg"><?php echo htmlspecialchars($clinic['clinicOwner'] ?? 'Not specified'); ?></p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                  <p class="text-gray-900 bg-gray-50 p-3 rounded-lg"><?php echo htmlspecialchars($clinic['cNumber']); ?></p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-700 mb-2">Location</label>
                  <p class="text-gray-900 bg-gray-50 p-3 rounded-lg"><?php echo htmlspecialchars($clinic['clinicAddress']); ?></p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Operating Schedule -->
      <div class="flex flex-col mt-5 p-5 bg-white w-full rounded-lg shadow-sm">
        <h1 class="font-semibold text-offblack-100 text-xl">Operating Schedule</h1>
        <div class="w-full p-2 flex flex-col text-grey-100 mt-5 border border-lightgrey-100 rounded-lg">
          <?php if (!empty($schedule)): ?>
            <?php foreach ($schedule as $index => $day): ?>
              <?php 
              $isOpen = $day['openTime'] !== '00:00:00' && $day['closeTime'] !== '00:00:00';
              $openTime = $isOpen ? date('g:i A', strtotime($day['openTime'])) : '-';
              $closeTime = $isOpen ? date('g:i A', strtotime($day['closeTime'])) : '-';
              ?>
              <div class="flex items-center justify-between py-3 <?php echo $index < count($schedule) - 1 ? 'border-b border-lightgrey-100' : ''; ?>">
                <span class="font-medium text-offblack-100"><?php echo htmlspecialchars($day['dayOfWeek']); ?></span>
                <div class="flex items-center gap-3">
                  <?php if ($isOpen): ?>
                    <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">Open</span>
                    <span class="text-grey-100"><?php echo $openTime; ?> - <?php echo $closeTime; ?></span>
                  <?php else: ?>
                    <span class="px-3 py-1 bg-red-100 text-red-800 text-sm rounded-full">Closed</span>
                    <span class="text-grey-100">-</span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="py-4 text-center text-grey-100">
              No schedule information available
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Offered Services -->
      <div class="flex flex-col mt-5 p-5 bg-white w-full rounded-lg shadow-sm border border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Offered Services</h2>
        <div class="p-6">
          <?php if (!empty($clinic['offeredServices'])): ?>
            <div class="services-container highlighted-services">
              <?php echo nl2br(htmlspecialchars($clinic['offeredServices'])); ?>
            </div>
          <?php else: ?>
            <p class="text-gray-500">No services information available</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    
    <?php else: ?>
    <div class="flex flex-col items-center justify-center mt-10 p-10 bg-white rounded-lg shadow-sm">
      <div class="text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m5 0v0" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No clinic data found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by adding your first clinic.</p>
        <div class="mt-6">
          <a href="add_clinic.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Add Clinic
          </a>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </main>

  <script>
    const sideBar = document.querySelector('#sideBar');
    function toggleSideBar() {
      sideBar.style.transform = (sideBar.style.transform === 'translateX(-100%)' || sideBar.style.transform === '') 
        ? 'translateX(0%)' 
        : 'translateX(-100%)';
    }

    const colorClasses = [
      'general-checkups',
      'vaccinations', 
      'surgery',
      'emergency-care',
      'dental-care',
      'grooming'
    ];

    function getRandomColor() {
      return colorClasses[Math.floor(Math.random() * colorClasses.length)];
    }

    function highlightServicesDisplay() {
      const servicesContainer = document.querySelector('.highlighted-services');
      if (!servicesContainer) return;
      
      let text = servicesContainer.innerHTML;
      
      text = text.replace(/<br\s*\/?>/gi, '\n');
      let services = text.split(/[\n,]+/).filter(service => service.trim() !== '');
      
      if (services.length <= 1 && services[0]) {
        services = services[0].split(/\s+/).filter(word => word.trim() !== '');
      }
      
      servicesContainer.innerHTML = '';
      
      services.forEach(service => {
        service = service.trim();
        if (service === '') return;
        
        let serviceElement = document.createElement('span');
        
        const randomColorClass = getRandomColor();
        serviceElement.className = `service-highlight ${randomColorClass}`;
        serviceElement.textContent = service;
        
        servicesContainer.appendChild(serviceElement);
      });
    }

    document.addEventListener('DOMContentLoaded', function() {
      highlightServicesDisplay();
    });
  </script>
</body>
</html>