<?php include_once '../sql/createDB.php'; 

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $clinicName = trim($_POST['clinic_name']);
    $clinicOwner = trim($_POST['owner_name']);
    $clinicEmail = trim($_POST['clinic_email']);
    $clinicPhone = trim($_POST['clinic_phone']);
    $offeredServices = trim($_POST['offered_services']);
    $clinicAddress = trim($_POST['clinic_address']);
    
    $clinicPhoto = '';
    if (isset($_FILES['clinic_photo']) && $_FILES['clinic_photo']['error'] == 0) {
        $uploadDir = 'media/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = 'photo_' . time() . '_' . $_FILES['clinic_photo']['name'];
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['clinic_photo']['tmp_name'], $targetPath)) {
            $clinicPhoto = $targetPath;
        }
    }
    
$proofFiles = [];
if (isset($_FILES['proof_files']) && count($_FILES['proof_files']['name']) > 0) {
    $uploadDir = 'media/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];

    foreach ($_FILES['proof_files']['name'] as $key => $name) {
        if ($_FILES['proof_files']['error'][$key] == 0) {
            $fileType = $_FILES['proof_files']['type'][$key];
            if (in_array($fileType, $allowedTypes)) {
                $fileName = 'proof_' . time() . '_' . basename($name);
                $targetPath = $uploadDir . $fileName;
                if (move_uploaded_file($_FILES['proof_files']['tmp_name'][$key], $targetPath)) {
                    $proofFiles[] = $targetPath;
                }
            }
        }
    }
}

$proofFile = json_encode($proofFiles);

    
    if (empty($clinicName) || empty($clinicOwner) || empty($clinicEmail) || empty($clinicPhone) || empty($clinicAddress)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    } elseif (empty($proofFile)) {
        $message = 'Please upload a proof document to verify clinic legitimacy.';
        $messageType = 'error';
    } else {
        $stmt = $conn->prepare("INSERT INTO clinic (clinicName, clinicOwner, email, cNumber, offeredServices, clinicAddress, proof, clinicPhoto) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $clinicName, $clinicOwner, $clinicEmail, $clinicPhone, $offeredServices, $clinicAddress, $proofFile, $clinicPhoto);
        
        if ($stmt->execute()) {
            $clinicId = $conn->insert_id;
            
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $scheduleInserted = true;
            
            foreach ($days as $day) {
                if (isset($_POST[strtolower($day) . '_enabled'])) {
                    $openTime = $_POST[strtolower($day) . '_open'] ?? '09:00';
                    $closeTime = $_POST[strtolower($day) . '_close'] ?? '17:00';
                    
                    $scheduleStmt = $conn->prepare("INSERT INTO clinic_schedule (clinicID, dayOfWeek, openTime, closeTime) VALUES (?, ?, ?, ?)");
                    $scheduleStmt->bind_param("isss", $clinicId, $day, $openTime, $closeTime);
                    
                    if (!$scheduleStmt->execute()) {
                        $scheduleInserted = false;
                        break;
                    }
                    $scheduleStmt->close();
                } else {
                    $scheduleStmt = $conn->prepare("INSERT INTO clinic_schedule (clinicID, dayOfWeek, openTime, closeTime) VALUES (?, ?, '00:00:00', '00:00:00')");
                    $scheduleStmt->bind_param("is", $clinicId, $day);
                    $scheduleStmt->execute();
                    $scheduleStmt->close();
                }
            }
            
            if ($scheduleInserted) {
                $message = 'Clinic added successfully!';
                $messageType = 'success';
                $_POST = array();
            } else {
                $message = 'Clinic added but there was an issue with the schedule.';
                $messageType = 'warning';
            }
        } else {
            if ($conn->errno == 1062) {
                $message = 'Clinic name or email already exists.';
                $messageType = 'error';
            } else {
                $message = 'Error adding clinic: ' . $conn->error;
                $messageType = 'error';
            }
        }
        $stmt->close();
    }
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
      .alert { padding: 1rem; margin: 1rem 0; border-radius: 0.5rem; }
      .alert-success { background-color: #d1edff; color: #0369a1; border: 1px solid #7dd3fc; }
      .alert-error { background-color: #fecaca; color: #dc2626; border: 1px solid #fca5a5; }
      .alert-warning { background-color: #fef3c7; color: #d97706; border: 1px solid #fcd34d; }
    
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
          <h1 class="font-semibold text-offblack-100 text-xl">Glydel Despojo</h1>
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
        <a href="all_clinics.html" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer hover:text-blue-500 p-3 nav-item">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
          </svg>
          <p class="ml-2">All Maps</p>
        </a>
      </div>
      <a href="logout.html" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer mt-20 hover:text-red-500 p-3 nav-item">
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
      <h1 class="font-bold text-offblack-100 text-4xl">Clinic Form</h1>
    </div>
    <div class="mb-5">
      <p class="text-grey-100 flex items-center"><img src="../media/circle-info-solid.svg" class="size-4 filter-grey" alt="info">&nbsp; Fill up the form for appointment scheduling and wait for the feedback of the clinic</p>
    </div>
  </div>

  <!-- Display messages -->
  <?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
      <?php echo htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data" class="flex flex-col items-center mt-5">
<div class="flex flex-col p-5 bg-white w-full rounded-lg shadow-sm">
    <h1 class="font-semibold text-offblack-100 text-xl">Basic Information</h1>

    <div class="grid grid-cols-2 gap-x-5 gap-y-2 mt-5">
        <div class="text-offblack-100 font-semibold">Clinic Name *</div>
        <div class="text-offblack-100 font-semibold">Clinic Email *</div>

        <div>
            <input 
                type="text" 
                name="clinic_name" 
                placeholder="Clinic name" 
                value="<?php echo htmlspecialchars($_POST['clinic_name'] ?? ''); ?>" 
                required 
                class="w-full px-2 py-2 border border-lightgrey-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-offblack-100 text-sm placeholder:text-sm"
            >
        </div>
        <div>
            <input 
                type="email" 
                name="clinic_email" 
                placeholder="Clinic email" 
                value="<?php echo htmlspecialchars($_POST['clinic_email'] ?? ''); ?>" 
                required 
                class="w-full px-2 py-2 border border-lightgrey-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-offblack-100 text-sm placeholder:text-sm"
            >
        </div>

        <div class="text-offblack-100 font-semibold">Owner Name *</div>
        <div class="text-offblack-100 font-semibold">Clinic Phone Number *</div>
        <div>
            <input 
                type="text" 
                name="owner_name" 
                placeholder="Owner name" 
                value="<?php echo htmlspecialchars($_POST['owner_name'] ?? ''); ?>" 
                required 
                class="w-full px-2 py-2 border border-lightgrey-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-offblack-100 text-sm placeholder:text-sm"
            >
        </div>
        <div>
            <input 
                type="tel" 
                name="clinic_phone" 
                placeholder="Clinic phone number" 
                value="<?php echo htmlspecialchars($_POST['clinic_phone'] ?? ''); ?>" 
                required 
                class="w-full px-2 py-2 border border-lightgrey-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-offblack-100 text-sm placeholder:text-sm"
            >
        </div>
    </div>
    <div class="mt-4">
        <div class="text-offblack-100 font-semibold mb-2">Offered Services *</div>
        <div>
            <textarea 
                name="offered_services" 
                cols="10" 
                rows="5" 
                placeholder="Enter offered services... (e.g., General checkup, vaccinations, surgery, dental care, grooming services, emergency care, boarding, laboratory tests)" 
                class="resize-none w-full px-2 py-2 border border-lightgrey-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-offblack-100 text-sm placeholder:text-sm"
            ><?php echo htmlspecialchars($_POST['offered_services'] ?? ''); ?></textarea>
        </div>
        <strong>Tip:</strong> Press Enter or start a new line to automatically add each service
    </div>
</div>

    <div class="flex flex-col mt-5 p-5 bg-white w-full rounded-lg shadow-sm">
        <h1 class="font-semibold text-offblack-100 text-xl">Location & Address</h1>
        <div class="text-offblack-100 font-semibold mt-5 mb-2">Clinic Address *</div>
        <div><input type="text" name="clinic_address" placeholder="Enter Complete Address" value="<?php echo htmlspecialchars($_POST['clinic_address'] ?? ''); ?>" required class="w-full px-2 py-2 border border-lightgrey-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-offblack-100 text-sm placeholder:text-sm"></div>
        <h1 class="font-semibold text-offblack-100 mt-5 mb-2">Click to pin location on map</h1>
    </div>

    <div class="flex flex-col mt-5 p-5 bg-white w-full rounded-lg shadow-sm">
        <h1 class="font-semibold text-offblack-100 text-xl">Operating Schedule</h1>
        
        <?php 
        $days = [
            'Monday' => 'monday',
            'Tuesday' => 'tuesday', 
            'Wednesday' => 'wednesday',
            'Thursday' => 'thursday',
            'Friday' => 'friday',
            'Saturday' => 'saturday',
            'Sunday' => 'sunday'
        ];
        
        foreach ($days as $day => $dayKey): 
        ?>
        <div class="w-full p-2 flex items-center justify-between text-grey-100 mt-2 border border-lightgrey-100 rounded-lg">
            <div class="flex items-center">
                <input type="checkbox" name="<?php echo $dayKey; ?>_enabled" id="<?php echo $dayKey; ?>" 
                       <?php echo (isset($_POST[$dayKey . '_enabled']) ? 'checked' : ''); ?>>
                <label for="<?php echo $dayKey; ?>">&nbsp; <?php echo $day; ?></label>
            </div>
            <div class="flex items-center gap-2">
                <select name="<?php echo $dayKey; ?>_open" class="border border-lightgrey-100 rounded px-2 py-1 text-sm">
                    <?php for ($h = 0; $h < 24; $h++): ?>
                        <?php for ($m = 0; $m < 60; $m += 30): ?>
                            <?php 
                            $time = sprintf("%02d:%02d", $h, $m);
                            $selected = (isset($_POST[$dayKey . '_open']) && $_POST[$dayKey . '_open'] == $time) ? 'selected' : '';
                            if (!isset($_POST[$dayKey . '_open']) && $time == '09:00') $selected = 'selected';
                            ?>
                            <option value="<?php echo $time; ?>" <?php echo $selected; ?>><?php echo date('g:i A', strtotime($time)); ?></option>
                        <?php endfor; ?>
                    <?php endfor; ?>
                </select>
                <span>to</span>
                <select name="<?php echo $dayKey; ?>_close" class="border border-lightgrey-100 rounded px-2 py-1 text-sm">
                    <?php for ($h = 0; $h < 24; $h++): ?>
                        <?php for ($m = 0; $m < 60; $m += 30): ?>
                            <?php 
                            $time = sprintf("%02d:%02d", $h, $m);
                            $selected = (isset($_POST[$dayKey . '_close']) && $_POST[$dayKey . '_close'] == $time) ? 'selected' : '';
                            if (!isset($_POST[$dayKey . '_close']) && $time == '17:00') $selected = 'selected';
                            ?>
                            <option value="<?php echo $time; ?>" <?php echo $selected; ?>><?php echo date('g:i A', strtotime($time)); ?></option>
                        <?php endfor; ?>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="flex flex-col mt-5 p-5 bg-white w-full rounded-lg shadow-sm">
        <h1 class="font-semibold text-offblack-100 text-xl">Verification & Media</h1>
        
   <!-- Proof Document  -->
<div class="mb-6">
  <h2 class="font-semibold text-offblack-100 mt-5 mb-2">Clinic Verification Document *</h2>
  <p class="text-sm text-grey-100 mb-3">Upload proof of clinic legitimacy (Business License, Veterinary License, Registration Certificate, etc.)</p>
  
  <div id="proofDrop" class="border-2  hover:border-blue-500 border-dashed border-lightgrey-100 rounded-md p-6 text-center cursor-pointer">
      <span class="font-medium text-blue-500">Upload a proof </span> or drag and drop
  <p class="text-xs text-gray-400 mt-3">PNG, JPG, PDF up to 10MB</p>
  <div class="text-xs text-grey-100 mt-2">
    <p><strong>Accepted documents:</strong></p>
    <p>• Business Registration/License</p>
    <p>• Veterinary Practice License</p>
    <p>• Professional Certification</p>
    <p>• Government-issued Permits</p>
  </div>
<input type="file" name="proof_files[]" id="proofInput" accept="image/*,.pdf" multiple required class="hidden">
  </div>

  <div id="proofStatus" class="mt-2 text-sm text-green-600 hidden items-center">
    <svg class="w-4 h-4 mr-1 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
    </svg>
    <span id="proofFileName"></span>
  </div>
</div>

<!-- Clinic Photo  -->
<div>
  <h2 class="font-semibold text-offblack-100 mb-2">Clinic Photo *</h2>
  <p class="text-sm text-grey-100 mb-3">Upload a photo of clinic exterior or interior</p>
  
  <div id="photoDrop" class="border-2 hover:border-blue-500 border-dashed border-lightgrey-100 rounded-md p-6 text-center cursor-pointer">
      <span class="font-medium text-blue-500">Upload a photo of clinic</span> or drag and drop
        <p class="text-xs text-gray-400 mt-3">PNG, JPG, PDF up to 10MB</p>
    <input type="file" name="clinic_photo" id="photoInput" accept="image/*" class="hidden">
  </div>

  <div id="photoStatus" class="mt-2 text-sm text-green-600 hidden items-center">
    <svg class="w-4 h-4 mr-1 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
    </svg>
    <span id="photoFileName"></span>
  </div>
  </div>

    <div class="flex justify-end gap-3 mt-5 w-full">
        <button type="reset" class="py-2 px-8 text-offblack-100 border border-lightgrey-100 rounded-md">
            Cancel
        </button>
        <button type="submit" class="flex items-center bg-blue-500 px-4 py-2 rounded-md text-white hover:bg-blue-600">
     Save Clinic
        </button>
    </div>
  </form>
  <script>
function setupDropZone(dropId, inputId, statusId, fileNameId, multiple = false) {
  const drop = document.getElementById(dropId);
  const input = document.getElementById(inputId);
  const status = document.getElementById(statusId);
  const fileName = document.getElementById(fileNameId);

  drop.addEventListener("click", () => input.click());

  drop.addEventListener("dragover", (e) => {
    e.preventDefault();
    drop.classList.add("border-blue-500", "bg-blue-50");
  });

  drop.addEventListener("dragleave", () => {
    drop.classList.remove("border-blue-500", "bg-blue-50");
  });

  drop.addEventListener("drop", (e) => {
    e.preventDefault();
    drop.classList.remove("border-blue-500", "bg-blue-50");
    if (e.dataTransfer.files.length) {
      input.files = e.dataTransfer.files;
      showFiles();
    }
  });

  input.addEventListener("change", showFiles);

  function showFiles() {
    if (input.files.length > 0) {
      fileName.innerHTML = "";
      Array.from(input.files).forEach(f => {
        fileName.innerHTML += `<div class="flex items-center gap-1">${f.name}</div>`;
      });
      status.classList.remove("hidden");
    }
  }
}

setupDropZone("proofDrop", "proofInput", "proofStatus", "proofFileName", true);
setupDropZone("photoDrop", "photoInput", "photoStatus", "photoFileName", false);

const sideBar = document.querySelector('#sideBar');

function toggleSideBar() {
    sideBar.style.transform = (sideBar.style.transform === 'translateX(-100%)' || sideBar.style.transform === '') ? 'translateX(0%)' : 'translateX(-100%)';
}
</script>

  </body>
</html>