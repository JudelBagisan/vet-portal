<?php
require_once("../sql/createDB.php");
session_start();

if (!isset($_SESSION['idLogin'])) {
    header("Location: ../homepage.php?noLogin");
    exit;
}

$idLogin = $_SESSION['idLogin'];

$clinicSql = "SELECT clinicID, clinicName FROM clinic";
$clinicResult = $conn->query($clinicSql);


$sql = "SELECT petId, petName, petImage_url FROM pets WHERE petOwnerId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idLogin);
$stmt->execute();
$result = $stmt->get_result();

$pets = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pets[] = $row;
    }
}

$clinicSchedules = [];
$scheduleSql = "SELECT cs.clinicID, cs.dayOfWeek, cs.openTime, cs.closeTime 
                FROM clinic_schedule cs";
$scheduleResult = $conn->query($scheduleSql);
if ($scheduleResult) {
    while ($row = $scheduleResult->fetch_assoc()) {
        if ($row['openTime'] !== '00:00:00' || $row['closeTime'] !== '00:00:00') {
            $clinicSchedules[$row['clinicID']][$row['dayOfWeek']] = [
                'openTime' => $row['openTime'],
                'closeTime' => $row['closeTime']
            ];
        }
    }
}

$appointmentsStmt = $conn->prepare("SELECT appointmentId, appointment_status, appointDate FROM petsappointment WHERE petOwnerId = ?");
$appointmentsStmt->bind_param("i", $idLogin);
$appointmentsStmt->execute();
$appointmentsResult = $appointmentsStmt->get_result();

$totalAppointments = 0;
$upcomingAppointments = 0;
$pendingAppointments = 0;

$appointmentsArray = [];
while ($row = $appointmentsResult->fetch_assoc()) {
    $appointmentsArray[] = $row;
    $totalAppointments++;
    
    if ($row['appointment_status'] == 'approved' && strtotime($row['appointDate']) >= time()) {
        $upcomingAppointments++;
    }
    if ($row['appointment_status'] == 'pending') {
        $pendingAppointments++;
    }
}

// Fetch clinics for dropdown
$clinicSql = "SELECT clinicID, clinicName FROM clinic";
$clinicResult = $conn->query($clinicSql);
$clinics = [];
if ($clinicResult && $clinicResult->num_rows > 0) {
    while ($row = $clinicResult->fetch_assoc()) {
        $clinics[] = $row;
    }
}
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
      .pet-card { transition: opacity 0.3s ease, transform 0.3s ease; }
      .pet-card.hidden { opacity: 0; transform: scale(0.95); display: none; }
      .time-slot-disabled { opacity: 0.5; cursor: not-allowed; }
      .clinic-closed-message { background-color: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 0.75rem; border-radius: 0.375rem; margin-top: 0.5rem; }
      .clinic-open-message { background-color: #f0f9ff; border: 1px solid #bae6fd; color: #0369a1; padding: 0.75rem; border-radius: 0.375rem; margin-top: 0.5rem; }
  </style>
</head>
<body class="min-h-screen bg-offwhite-100">

<?php
// Display success or error messages
if (isset($_SESSION['success_message'])) {
    echo '<div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg z-50" id="successMessage">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span>' . htmlspecialchars($_SESSION['success_message']) . '</span>
                <button onclick="closeMessage(\'successMessage\')" class="ml-4 text-green-500 hover:text-green-700">×</button>
            </div>
          </div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg z-50" id="errorMessage">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span>' . htmlspecialchars($_SESSION['error_message']) . '</span>
                <button onclick="closeMessage(\'errorMessage\')" class="ml-4 text-red-500 hover:text-red-700">×</button>
            </div>
          </div>';
    unset($_SESSION['error_message']);
}
?>

<!-- Sidebar -->
<div class="fixed w-[60%] md:w-[18%] bg-white h-full p-6 flex flex-col text-grey-100 z-50 shadow-2xl" id="sideBar" style="transform: translateX(-100%);">
  <div class="flex flex-col">
    <div class="flex justify-end">
      <img src="../media/burger-short.png" alt="nav-icon point" class="w-10 cursor-pointer" onclick="toggleSideBar()">
    </div>
    <div class="flex mt-20">
      <div class="bg-blue-500 w-12 h-12 filter-grey rounded-md"></div>
      <div class="ml-2">
        <h1 class="font-semibold text-offblack-100 text-xl">Glydel Despojo</h1>
        <p class="text-grey-100">Fill the form below</p>
      </div>  
    </div>
    <div class="flex flex-col gap-3 mt-20">
      <a href="dashboard.html" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer hover:text-blue-500 p-3 nav-iemt">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
      </svg>
        <p class="ml-2">Dashboard</p>
      </a>
      <a href="clinic_list.php" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer hover:text-blue-500 p-3 nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
        </svg>
        <p class="ml-2">Clinic Maps</p>
      </a>
      <a href="pet_list.php" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer hover:text-blue-500 p-3 nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
        </svg>
        <p class="ml-2">Pet List</p>
      </a>
      <a href="appointment_list.php" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer hover:text-blue-500 p-3 nav-item">
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

<div class="p-10 w-full h-full flex flex-col">
  <div class="flex flex-col border-b border-b-lightgrey-100">
    <div>
      <img src="../media/burger-long.png" alt="nav-icon" class="w-10 cursor-pointer" onclick="toggleSideBar()">
    </div>
    <div class="mt-10">
        <h1 class="font-bold text-offblack-100 text-4xl">Appointments  Management</h1>
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
          <p class="text-grey-100">Upcoming</p>
          <h2 class="text-2xl text-green-500 font-bold"><?= $upcomingAppointments ?></h2>
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


<div class="bg-white rounded-xl shadow p-6 mt-10">
  <h2 class="text-xl font-bold text-offblack-100 mb-1">Appointment Form</h2>
  <p class="text-gray-500 text-sm mb-6 flex items-center gap-2">
    <span class="text-gray-400">
      <img src="../media/circle-exclamation-solid-full.svg" alt="" class="w-5 h-5 filter-grey">
    </span> Fill up the form for appointment scheduling and wait for the feedback of the clinic
  </p>

  <!-- infoo -->
  <form method="POST" action="process_appointment.php" class="grid grid-cols-1 md:grid-cols-2 gap-6" id="appointmentForm">
    <div>
      <label class="block text-sm font-semibold text-offblack-100 mb-1">Select Clinic</label>
  <select name="clinicID" id="clinicSelect" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">Select clinic</option>
      <?php
      $clinicSql = "SELECT clinicID, clinicName FROM clinic";
      $clinicResult = $conn->query($clinicSql);

      if ($clinicResult && $clinicResult->num_rows > 0):
          while ($clinic = $clinicResult->fetch_assoc()): ?>
              <option value="<?php echo htmlspecialchars($clinic['clinicID']); ?>">
                  <?php echo htmlspecialchars($clinic['clinicName']); ?>
              </option>
          <?php endwhile;
      else: ?>
          <option value="" disabled>No clinics available</option>
      <?php endif; ?>
    </select>
    </div>

      <div>
      <label class="block text-sm font-semibold text-offblack-100 mb-1">Or Find on Map</label>
      <a href="" class="w-full flex items-center justify-center gap-2 border border-dashed border-gray-300 rounded-md px-3 py-2 text-gray-500 hover:border-blue-500 hover:text-blue-500 transition">
        <img src="../media/location-dot-solid-full.svg" alt="" class="w-5 h-5" />
        View Map
      </a>
    </div>

    <div>
      <label class="block text-sm font-semibold text-offblack-100 mb-1">Appointment Date</label>
      <input type="date" id="appointmentDate" name="appointmentDate" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
      <div id="dateMessage" class="hidden"></div>
    </div>

    <div>
      <label class="block text-sm font-semibold text-offblack-100 mb-1">Preferred Time Slot</label>
      <select id="timeSlotSelect" name="timeSlot" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" disabled>
        <option>Select date first to view available time slots</option>
      </select>
      <div id="timeSlotMessage" class="hidden"></div>
    </div>

    <div>
      <label class="block text-sm font-semibold text-offblack-100 mb-1">Select Pet</label>
   <select name="petId" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
  <option value="" class="text-txtgray-100 p-2">Select pet</option>
      <?php if (!empty($pets)): ?>
          <?php foreach ($pets as $pet): ?>
              <option value="<?php echo htmlspecialchars($pet['petId']); ?>" 
                      data-name="<?php echo htmlspecialchars($pet['petName']); ?>" 
                      data-image="<?php echo htmlspecialchars($pet['petImage_url']); ?>" 
                      class="text-txtgray-100 p-2">
                  <?php echo htmlspecialchars($pet['petName']); ?>
              </option>
          <?php endforeach; ?>
      <?php else: ?>
          <option value="" disabled>No pets available</option>
      <?php endif; ?>
</select>

    </div>

    <div>
      <label class="block text-sm font-semibold text-offblack-100 mb-1">Purpose of Visit</label>
  <textarea name="purpose" placeholder="purpose of your visit" rows="4" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
    </div>

    <div class="md:col-span-2 flex justify-end">
      <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded-md font-semibold hover:bg-blue-600 transition" id="submitBtn">
        Set Appointment
      </button>
    </div>
  </form>
</div>
  <script>
      const clinicSchedules = <?php echo json_encode($clinicSchedules); ?>;
      
      const clinicSelect = document.getElementById('clinicSelect');
      const appointmentDate = document.getElementById('appointmentDate');
      const timeSlotSelect = document.getElementById('timeSlotSelect');
      const dateMessage = document.getElementById('dateMessage');
      const timeSlotMessage = document.getElementById('timeSlotMessage');
      
      const today = new Date().toISOString().split('T')[0];
      appointmentDate.min = today;
      
      clinicSelect.addEventListener('change', handleClinicChange);
      appointmentDate.addEventListener('change', handleDateChange);
      timeSlotSelect.addEventListener('click', handleTimeSlotClick);
      
      function handleClinicChange() {
          appointmentDate.value = '';
          resetTimeSlots();
          hideMessage();
          hideTimeSlotMessage();
      }
      
      function handleTimeSlotClick() {
          const selectedClinic = clinicSelect.value;
          const selectedDate = appointmentDate.value;
          
          if (!selectedClinic) {
              showTimeSlotMessage('Please select a clinic first.', 'error');
              return;
          }
          
          if (!selectedDate) {
              showTimeSlotMessage('Please select a date first to view available time slots.', 'error');
              return;
          }
          
          hideTimeSlotMessage();
      }
      
      function handleDateChange() {
          const selectedClinic = clinicSelect.value;
          const selectedDate = appointmentDate.value;
          
          hideTimeSlotMessage();
          
          console.log('Selected clinic:', selectedClinic);
          console.log('Selected date:', selectedDate);
          console.log('All clinic schedules:', clinicSchedules);
          
          if (!selectedClinic) {
              showMessage('Please select a clinic first.', 'error');
              resetTimeSlots();
              return;
          }
          
          if (!selectedDate) {
              resetTimeSlots();
              hideMessage();
              return;
          }
          
          const dayOfWeek = getDayOfWeek(selectedDate);
          console.log('Day of week:', dayOfWeek);
          
          const clinicSchedule = clinicSchedules[selectedClinic];
          console.log('Clinic schedule for selected clinic:', clinicSchedule);
          
          if (clinicSchedule && clinicSchedule[dayOfWeek]) {
              const schedule = clinicSchedule[dayOfWeek];
              console.log('Schedule for this day:', schedule);
              generateTimeSlots(schedule.openTime, schedule.closeTime);
              showMessage('Please select an available time slot for your appointment. Slots are shown based on the clinic\'s operating hours for the chosen day.', 'success');
          } else {
              resetTimeSlots();
              showMessage('The clinic is closed on this day. Please choose another date when the clinic is open.', 'error');
          }
      }
      
      function getDayOfWeek(dateString) {
          const date = new Date(dateString);
          const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
          return days[date.getDay()];
      }
      
      function generateTimeSlots(openTime, closeTime) {
          console.log('Generating time slots from', openTime, 'to', closeTime);
          
          timeSlotSelect.innerHTML = '<option value="">Select time slot</option>';
          
          const openMinutes = timeToMinutes(openTime);
          const closeMinutes = timeToMinutes(closeTime);
          
          console.log('Open minutes:', openMinutes, 'Close minutes:', closeMinutes);
          
          const slotDuration = 180; 
          
          for (let start = openMinutes; start + slotDuration <= closeMinutes; start += slotDuration) {
              const end = start + slotDuration;
              const startTime = minutesToTime(start);
              const endTime = minutesToTime(end);
              
              console.log('Adding slot:', startTime, '-', endTime);
              
              const option = document.createElement('option');
              option.value = `${startTime}-${endTime}`;
              option.textContent = `${formatTime(startTime)} - ${formatTime(endTime)}`;
              timeSlotSelect.appendChild(option);
          }
          
          console.log('Total options in select:', timeSlotSelect.options.length);
          
          timeSlotSelect.disabled = false;
      }
      
      function timeToMinutes(timeString) {
          const [hours, minutes] = timeString.split(':').map(Number);
          return hours * 60 + minutes;
      }
      
      function minutesToTime(minutes) {
          const hours = Math.floor(minutes / 60);
          const mins = minutes % 60;
          return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
      }
      
      function formatTime(timeString) {
          const [hours, minutes] = timeString.split(':').map(Number);
          const period = hours >= 12 ? 'PM' : 'AM';
          const displayHours = hours % 12 || 12;
          return `${displayHours}:${minutes.toString().padStart(2, '0')} ${period}`;
      }
      
      function resetTimeSlots() {
          timeSlotSelect.innerHTML = '<option value="">Select date first to view available time slots</option>';
          timeSlotSelect.disabled = true;
      }
      
      function showMessage(message, type) {
          dateMessage.className = type === 'error' ? 'clinic-closed-message' : 'clinic-open-message';
          dateMessage.textContent = message;
          dateMessage.classList.remove('hidden');
      }
      
      function hideMessage() {
          dateMessage.classList.add('hidden');
      }
      
      function showTimeSlotMessage(message, type) {
          timeSlotMessage.className = type === 'error' ? 'clinic-closed-message' : 'clinic-open-message';
          timeSlotMessage.textContent = message;
          timeSlotMessage.classList.remove('hidden');
      }
      
      function hideTimeSlotMessage() {
          timeSlotMessage.classList.add('hidden');
      }
      
      const sideBar = document.querySelector('#sideBar');

      function toggleSideBar() {
          sideBar.style.transform = (sideBar.style.transform === 'translateX(-100%)' || sideBar.style.transform === '') ? 'translateX(0%)' : 'translateX(-100%)';
      }
      
      // Form validation before submission
      document.getElementById('appointmentForm').addEventListener('submit', function(e) {
          const clinicSelect = document.getElementById('clinicSelect');
          const appointmentDate = document.getElementById('appointmentDate');
          const timeSlotSelect = document.getElementById('timeSlotSelect');
          const petSelect = document.querySelector('select[name="petId"]');
          const purposeTextarea = document.querySelector('textarea[name="purpose"]');
          
          let isValid = true;
          let errorMessage = '';
          
          if (!clinicSelect.value) {
              isValid = false;
              errorMessage += 'Please select a clinic.\n';
          }
          
          if (!appointmentDate.value) {
              isValid = false;
              errorMessage += 'Please select an appointment date.\n';
          }
          
          if (!timeSlotSelect.value || timeSlotSelect.disabled) {
              isValid = false;
              errorMessage += 'Please select a time slot.\n';
          }
          
          if (!petSelect.value) {
              isValid = false;
              errorMessage += 'Please select a pet.\n';
          }
          
          if (!purposeTextarea.value.trim()) {
              isValid = false;
              errorMessage += 'Please enter the purpose of visit.\n';
          }
          
          if (!isValid) {
              e.preventDefault();
              alert('Please complete all required fields:\n\n' + errorMessage);
              return false;
          }
          
          const submitBtn = document.getElementById('submitBtn');
          submitBtn.disabled = true;
          submitBtn.textContent = 'Processing...';
          
          return true;
      });
      
      function closeMessage(messageId) {
          const message = document.getElementById(messageId);
          if (message) {
              message.style.display = 'none';
          }
      }
      
      setTimeout(function() {
          const successMessage = document.getElementById('successMessage');
          const errorMessage = document.getElementById('errorMessage');
          
          if (successMessage) {
              successMessage.style.display = 'none';
          }
          if (errorMessage) {
              errorMessage.style.display = 'none';
          }
      }, 5000);
  </script>
</body>
</html>

