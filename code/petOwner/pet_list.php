<?php
session_start();
require_once("../sql/createDB.php");

if (!isset($_SESSION['idLogin'])) {
    header("Location: ../homepage.php?noLogin");
    exit;
}

$idLogin = $_SESSION['idLogin'];

//  for edit form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_pet'])) {
    $petId = $_POST['petId'];
    $petName = $_POST['petName'];
    $species = $_POST['species'];
    $breed = $_POST['breed'];
    $birthDate = $_POST['birthDate'];
    $sex = $_POST['sex'];
    $age = $_POST['age'];

    $checkStmt = $conn->prepare("SELECT petImage_url FROM pets WHERE petId = ? AND petOwnerId = ?");
    $checkStmt->bind_param("ii", $petId, $idLogin);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        echo "<script>alert('Unauthorized access or pet not found.'); window.location.href='pet_list.php';</script>";
        exit();
    }
    
    $currentPet = $checkResult->fetch_assoc();
    $petImage_url = $currentPet['petImage_url'];

  
    if (!empty($_FILES['petImage']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES['petImage']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['petImage']['tmp_name'], $targetFile)) {
            if ($petImage_url && file_exists($petImage_url)) {
                unlink($petImage_url);
            }
            $petImage_url = $targetFile;
        }
    }

    $stmt = $conn->prepare("UPDATE pets SET petName = ?, species = ?, breed = ?, birthDate = ?, sex = ?, age = ?, petImage_url = ? WHERE petId = ? AND petOwnerId = ?");
    $stmt->bind_param("sssssisii", $petName, $species, $breed, $birthDate, $sex, $age, $petImage_url, $petId, $idLogin);

    if ($stmt->execute()) {
        echo "<script>alert('Pet updated successfully!'); window.location.href='pet_list.php';</script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// for delete pet
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_pet'])) {
    $petId = $_POST['petId'];
    
    $stmt = $conn->prepare("SELECT petImage_url FROM pets WHERE petId = ? AND petOwnerId = ?");
    $stmt->bind_param("ii", $petId, $idLogin);
    $stmt->execute();
    $result = $stmt->get_result();
    $pet = $result->fetch_assoc();
    
    if ($pet) {
        if ($pet['petImage_url'] && file_exists($pet['petImage_url'])) {
            unlink($pet['petImage_url']);
        }
        
        $deleteStmt = $conn->prepare("DELETE FROM pets WHERE petId = ? AND petOwnerId = ?");
        $deleteStmt->bind_param("ii", $petId, $idLogin);
        
        if ($deleteStmt->execute()) {
            echo "<script>alert('Pet deleted successfully!'); window.location.href='pet_list.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error deleting pet.');</script>";
        }
    } else {
        echo "<script>alert('Pet not found or unauthorized access.');</script>";
    }
}

// for pet form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_pet'])) {
    $petName   = $_POST['petName'];
    $species   = $_POST['species'];
    $breed     = $_POST['breed'];
    $birthDate = $_POST['birthDate'];
    $sex       = $_POST['sex'];
    $age       = $_POST['age'];

    $petImage_url = null;
    if (!empty($_FILES['petImage']['name'])) {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES['petImage']['name']);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['petImage']['tmp_name'], $targetFile)) {
            $petImage_url = $targetFile;
        }
    }

    $stmt = $conn->prepare("INSERT INTO pets (petOwnerId, petName, species, breed, birthDate, sex, age, petImage_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssis", $idLogin, $petName, $species, $breed, $birthDate, $sex, $age, $petImage_url);

    if ($stmt->execute()) {
        echo "<script>alert('Pet added successfully!'); window.location.href='pet_list.php';</script>";
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// for fetching all pets
$pets = [];
$sql = "SELECT * FROM pets WHERE petOwnerId = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idLogin);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $pets[] = $row;
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
      .petlist { background-color: oklch(62.3% 0.214 259.815); color: white;}
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
      <h1 class="font-bold text-offblack-100 text-4xl">Welcome to Pet List</h1>
    </div>
    <div class="mb-5">
      <p class="text-grey-100">In this section you can add your pet so that you can appoint faster</p>
    </div>
  </div>

<!-- Addpet container -->
<div id="addPetModal" class="fixed bg-offblack-100/30 inset-0 z-50 hidden items-center justify-center">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-auto p-5 relative">
    <button type="button" class="absolute top-4 right-4 text-gray-400 cursor-pointer hover:text-gray-500 transition-all" onclick="togglePetModal()">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    <div class="mb-4">
      <h2 class="text-lg font-semibold text-offblack-100">Add Pet</h2>
      <p class="text-grey-100 text-sm mt-1">Fill up the form and add pet.</p>
    </div>

 <form class="space-y-3" method="POST" enctype="multipart/form-data">
  <div>
    <label class="block text-sm font-medium text-offblack-100 mb-1">Pet Name</label>
    <input type="text" name="petName" required placeholder="Enter pet name" class="w-full px-2 py-2 border border-lightgrey-100 rounded-md">
  </div>

  <div class="flex gap-3">
    <div class="flex-1 flex flex-col">
      <label class="text-sm font-medium text-offblack-100 mb-1">Species</label>
      <input type="text" name="species" required placeholder="Enter species" class="w-full px-3 py-2 border border-lightgrey-100 rounded-md">
    </div>
    <div class="flex-1 flex flex-col">
      <label class="text-sm font-medium text-offblack-100 mb-1">Breed</label>
      <input type="text" name="breed" required placeholder="Enter breed" class="w-full px-3 py-2 border border-lightgrey-100 rounded-md">
    </div>
  </div>

  <div>
    <label class="block text-sm font-medium text-offblack-100 mb-1">Birthdate</label>
    <input type="date" name="birthDate" required class="w-full px-3 py-2 border border-lightgrey-100 rounded-md">
  </div>

  <div class="flex gap-3">
    <div class="flex-1 flex flex-col">
      <label class="text-sm font-medium text-offblack-100 mb-1">Age</label>
      <input type="number" name="age" required placeholder="Enter age" class="w-full px-3 py-2 border border-lightgrey-100 rounded-md">
    </div>
    <div class="flex-1 flex flex-col">
      <label class="text-sm font-medium text-offblack-100 mb-1">Sex</label>
      <select name="sex" required class="w-full px-3 py-2 border border-lightgrey-100 rounded-md">
        <option value="">Select sex</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
      </select>
    </div>
    </div>

    <div>
  <label class="block text-sm font-medium text-offblack-100 mb-1">Pet Photo</label>

<div id="dropArea" 
     class="border-2 border-dashed border-lightgrey-100 rounded-md p-4 text-center hover:border-blue-500 transition-all cursor-pointer">
        <svg class="w-10 h-10 text-gray-400 mx-auto" fill="none" stroke="currentColor" stroke-width="1.5"
            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 16v-4m0 0l-2 2m2-2l2 2m6-2a4 4 0 00-3.65-3.995A5.5 5.5 0 006.5 9a5.5 5.5 0 00-.36 10.995H18a4 4 0 000-8z" />
        </svg>
    <p class="mt-1 text-sm text-grey-100">
      <span class="font-medium text-blue-500">Upload a file</span> or drag and drop
    </p>
    <p id="fileName" class="text-xs text-grey-100 mt-1"></p>
  </div>

  <input type="file" id="petImage" name="petImage" class="hidden" accept="image/*">
</div>


  <button type="submit" name="add_pet" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
    Add Pet
  </button>
</form>

  </div>
</div>

<!-- Edit Pet form -->
<div id="editPetModal" class="fixed bg-offblack-100/30 inset-0 z-50 hidden items-center justify-center">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-auto p-5 relative">
    <button type="button" class="absolute top-4 right-4 text-gray-400 cursor-pointer hover:text-gray-500 transition-all" onclick="toggleEditModal()">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>

    <div class="mb-4">
      <h2 class="text-lg font-semibold text-offblack-100">Edit Pet</h2>
      <p class="text-grey-100 text-sm mt-1">Update your pet's information.</p>
    </div>

    <form class="space-y-3" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="petId" id="editPetId">
      
      <div>
        <label class="block text-sm font-medium text-offblack-100 mb-1">Pet Name</label>
        <input type="text" name="petName" id="editPetName" required placeholder="Enter pet name" class="w-full px-2 py-2 border border-lightgrey-100 rounded-md">
      </div>

      <div class="flex gap-3">
        <div class="flex-1 flex flex-col">
          <label class="text-sm font-medium text-offblack-100 mb-1">Species</label>
          <input type="text" name="species" id="editSpecies" required placeholder="Enter species" class="w-full px-3 py-2 border border-lightgrey-100 rounded-md">
        </div>
        <div class="flex-1 flex flex-col">
          <label class="text-sm font-medium text-offblack-100 mb-1">Breed</label>
          <input type="text" name="breed" id="editBreed" required placeholder="Enter breed" class="w-full px-3 py-2 border border-lightgrey-100 rounded-md">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-offblack-100 mb-1">Birthdate</label>
        <input type="date" name="birthDate" id="editBirthDate" required class="w-full px-3 py-2 border border-lightgrey-100 rounded-md">
      </div>

      <div class="flex gap-3">
        <div class="flex-1 flex flex-col">
          <label class="text-sm font-medium text-offblack-100 mb-1">Age</label>
          <input type="number" name="age" id="editAge" required placeholder="Enter age" class="w-full px-3 py-2 border border-lightgrey-100 rounded-md">
        </div>
        <div class="flex-1 flex flex-col">
          <label class="text-sm font-medium text-offblack-100 mb-1">Sex</label>
          <select name="sex" id="editSex" required class="w-full px-3 py-2 border border-lightgrey-100 rounded-md">
            <option value="">Select sex</option>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-offblack-100 mb-1">Pet Photo</label>
        <div class="mb-2">
          <img id="currentPetImage" src="" alt="Current pet image" class="w-20 h-20 rounded-md object-cover border">
          <p class="text-xs text-grey-100 mt-1">Current image (leave empty to keep current image)</p>
        </div>
        
        <div id="editDropArea" 
             class="border-2 border-dashed border-lightgrey-100 rounded-md p-4 text-center hover:border-blue-500 transition-all cursor-pointer">
          <svg class="mx-auto h-8 w-8 text-grey-100" stroke="currentColor" fill="none" viewBox="0 0 48 48">
            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" 
                  stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <p class="mt-1 text-sm text-grey-100">
            <span class="font-medium text-blue-500">Upload new image</span> or drag and drop
          </p>
          <p id="editFileName" class="text-xs text-grey-100 mt-1"></p>
        </div>

        <input type="file" id="editPetImage" name="petImage" class="hidden" accept="image/*">
      </div>

      <button type="submit" name="edit_pet" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600">
        Update Pet
      </button>
    </form>
  </div>
</div>

<!-- Delete Confirmation  -->
<div id="deleteModal" class="fixed bg-offblack-100/30 inset-0 z-50 hidden items-center justify-center">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-auto p-6 relative">
    <div class="text-center">
      
      <div class="flex items-center justify-center gap-2 mb-4">
        <div class="flex items-center justify-center h-10 w-10 rounded-full bg-red-100">
      <svg class="h-10 w-6 filter-red transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
  </svg>  
        </div>
        <h3 class="text-lg font-medium text-red-500">Delete Pet</h3>
      </div>

      <p class="text-sm text-gray-500 mb-6">
        Are you sure you want to delete this pet? This action cannot be undone.
      </p>
      
      <div class="flex gap-3">
        <button onclick="cancelDelete()" 
          class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 transition-colors">
          Cancel
        </button>
        <button onclick="confirmDelete()" 
          class="flex-1 bg-red-500 text-gray-700 py-2 px-4 rounded-md hover:bg-red-600 transition-colors">
          Delete
        </button>
      </div>
    </div>
  </div>
</div>


  <!-- dashcard -->
 <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-8">
      <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow transform transition-all duration-300 ease-in-out hover:-translate-y-2 hover:shadow-lg cursor-pointer">
        <div>
          <p class="text-grey-100">Pets Added</p>
          <h2 class="text-2xl text-blue-500 font-bold"><?= count($pets) ?></h2>
        </div>
        <div class="w-10 h-10 flex items-center justify-center bg-blue-100 rounded-md">
          <img src="../media/paw-solid.svg" alt="pet-icon"  class="w-8 h-8 filter-blue  ">
        </div>
      </div>
      <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow transform transition-all duration-300 ease-in-out hover:-translate-y-2 hover:shadow-lg cursor-pointer">
        <div>
          <p class="text-grey-100">Upcoming</p>
          <h2 class="text-2xl text-green-500 font-bold">0</h2>
        </div>
        <div class="w-10 h-10 flex items-center justify-center bg-green-100 rounded-md">
          <img src="../media/calendar-solid.svg" alt="calendar-icon" class="w-8 h-8 filter-green">
        </div>
      </div>
      <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow transform transition-all duration-300 ease-in-out hover:-translate-y-2 hover:shadow-lg cursor-pointer">
        <div>
          <p class="text-grey-100">Total Appointments</p>
          <h2 class="text-2xl text-yellow-500 font-bold">12</h2>
        </div>
        <div class="w-10 h-10 flex items-center justify-center bg-yellow-100 rounded-md">
          <img src="../media/clock-solid.svg" alt="clock-icon" class="w-8 h-8 filter-yellow">
        </div>
      </div>
    </div>

  <!-- Search and buttons -->
  <div class="flex flex-col md:flex-row justify-between items-center mt-8">
    <div class="w-full md:w-1/3 relative z-0">
      <input type="text" id="searchInput" placeholder="Search Pets" class="w-full px-4 py-2 rounded-md border border-lightgrey-100 focus:outline-none focus:border-blue-500">
      <span class="absolute inset-y-0 right-0 flex items-center pr-3">
      <img src="../media/magnifying-glass-solid.svg" alt="search" class="w-5 h-5 text-gray-400 hover:filter-blue transition">
      </span>
    </div>
    <button onclick="togglePetModal()" class="add-pet-btn hidden lg:inline mt-4 md:mt-0 md:ml-4 bg-blue-500 text-white px-4 py-2 rounded-md items-center hover:bg-blue-600 transition">
      <img src="../media/plus-solid.svg" class="w-5 h-5 filter-white inline-block mr-2">
      <span class="text-lg">Add Pet</span>
    </button>
  </div>

<!-- Pet info -->
<div id="petContainer" class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-10">
    <?php if (count($pets) > 0): ?>
      <?php foreach ($pets as $pet): ?>
        <div class="pet-card flex bg-white p-5 rounded-xl shadow justify-between items-start" 
             data-pet-name="<?= htmlspecialchars(strtolower($pet['petName'])) ?>"
             data-species="<?= htmlspecialchars(strtolower($pet['species'])) ?>"
             data-breed="<?= htmlspecialchars(strtolower($pet['breed'])) ?>"
             data-sex="<?= htmlspecialchars(strtolower($pet['sex'])) ?>">
          <div class="flex gap-4">
            <img src="<?= $pet['petImage_url'] ? htmlspecialchars($pet['petImage_url']) : '../media/default-pet.png' ?>" 
                 alt="pet" class="w-20 h-20 rounded-md object-cover">
            <div>
              <h2 class="font-bold text-lg"><?= htmlspecialchars($pet['petName']) ?></h2>
              <p class="text-sm text-grey-100">Species: <?= htmlspecialchars($pet['species']) ?></p>
              <p class="text-sm text-grey-100">Breed: <?= htmlspecialchars($pet['breed']) ?></p>
              <p class="text-sm text-grey-100">Birthdate: <?= htmlspecialchars($pet['birthDate']) ?></p>
              <p class="text-sm text-grey-100">Sex: <?= htmlspecialchars($pet['sex']) ?></p>
              <p class="text-sm text-grey-100">Age: <?= htmlspecialchars($pet['age']) ?></p>
            </div>
          </div>
          <div class="flex flex-row gap-x-2 items-center">
            <img src="../media/pen-to-square-solid.svg" alt="edit" class="w-5 cursor-pointer filter-blue hover:scale-110 transition-transform" onclick="editPet(<?= $pet['petId'] ?>, '<?= htmlspecialchars($pet['petName'], ENT_QUOTES) ?>', '<?= htmlspecialchars($pet['species'], ENT_QUOTES) ?>', '<?= htmlspecialchars($pet['breed'], ENT_QUOTES) ?>', '<?= $pet['birthDate'] ?>', '<?= htmlspecialchars($pet['sex'], ENT_QUOTES) ?>', <?= $pet['age'] ?>, '<?= htmlspecialchars($pet['petImage_url'], ENT_QUOTES) ?>')">
            <img src="../media/trash-solid.svg" alt="delete" class="w-5 cursor-pointer filter-red hover:scale-110 transition-transform" onclick="deletePet(<?= $pet['petId'] ?>)">
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
  <div class=" text-center py-10 col-span-2">
        <div class="text-6xl text-grey-100 mb-4">🐾</div>
        <h3 class="text-xl font-semibold text-offblack-100 mb-2">No pets found</h3>
        <p class="text-grey-100 mb-4">You haven't added any pets yet.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- No results message -->
  <div id="noResults" class="hidden text-center py-10 col-span-2">
    <div class="text-6xl text-grey-100 mb-4">🐾</div>
    <h3 class="text-xl font-semibold text-offblack-100 mb-2">No pets found</h3>
    <p class="text-grey-100 mb-4">Review your search</p>
  </div>
</div>

     <a href="add_Pet.html" class="lg:hidden mr-4 sm:mr-0" id="addButtonMobile">
        <button class=" rounded-md fixed bg-blue-500 bottom-10 right-10 px-3 py-3 items-center text-white text-sm font-medium 
       hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-primary-300">
            <img src="../media/plus-solid.svg" class="w-6 h-6 filter-white
            inline-block ">
        </button>
    </a>

  <!-- Mobile view-->
  <button onclick="togglePetModal()" class="add-pet-btn lg:hidden fixed bg-blue-500 bottom-10 right-10 px-3 py-3 text-white text-sm font-medium rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-primary-300">
    <img src="../media/plus-solid.svg" class="w-6 h-6 filter-white inline-block">
  </button>

  <form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="delete_pet" value="1">
    <input type="hidden" name="petId" id="deletePetId">
  </form>

  <script>
   const sideBar = document.querySelector('#sideBar');

      function toggleSideBar() {
          sideBar.style.transform = (sideBar.style.transform === 'translateX(-100%)' || sideBar.style.transform === '') ? 'translateX(0%)' : 'translateX(-100%)';
      }

  const petModal = document.querySelector('#addPetModal');
  function togglePetModal() {
    if(petModal.style.display === 'none' || petModal.style.display == '') {
      petModal.style.display = 'flex';
    }else {
      petModal.style.display = 'none';
    }
  }

  const dropArea = document.getElementById('dropArea');
  const petImageInput = document.getElementById('petImage');
  const fileNameDisplay = document.getElementById('fileName');

  dropArea.addEventListener('click', () => petImageInput.click());

  petImageInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
      fileNameDisplay.textContent = e.target.files[0].name;
    }
  });

  // drag & drop support
  dropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropArea.classList.add('border-blue-500', 'bg-blue-50');
  });

  dropArea.addEventListener('dragleave', () => {
    dropArea.classList.remove('border-blue-500', 'bg-blue-50');
  });

  dropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    dropArea.classList.remove('border-blue-500', 'bg-blue-50');

    if (e.dataTransfer.files.length > 0) {
      petImageInput.files = e.dataTransfer.files;
      fileNameDisplay.textContent = e.dataTransfer.files[0].name;
    }
  });

  // Edit Pet  
  const editModal = document.getElementById('editPetModal');
  const editDropArea = document.getElementById('editDropArea');
  const editPetImageInput = document.getElementById('editPetImage');
  const editFileNameDisplay = document.getElementById('editFileName');

  function toggleEditModal() {
    if(editModal.style.display === 'none' || editModal.style.display == '') {
      editModal.style.display = 'flex';
    } else {
      editModal.style.display = 'none';
    }
  }

  function editPet(petId, petName, species, breed, birthDate, sex, age, imageUrl) {
    document.getElementById('editPetId').value = petId;
    document.getElementById('editPetName').value = petName;
    document.getElementById('editSpecies').value = species;
    document.getElementById('editBreed').value = breed;
    document.getElementById('editBirthDate').value = birthDate;
    document.getElementById('editSex').value = sex;
    document.getElementById('editAge').value = age;
    
    const currentImageElement = document.getElementById('currentPetImage');
    if (imageUrl && imageUrl !== '') {
      currentImageElement.src = imageUrl;
      currentImageElement.style.display = 'block';
    } else {
      currentImageElement.src = '../media/default-pet.png';
      currentImageElement.style.display = 'block';
    }
    
    editFileNameDisplay.textContent = '';
    
    toggleEditModal();
  }

  editDropArea.addEventListener('click', () => editPetImageInput.click());

  editPetImageInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
      editFileNameDisplay.textContent = e.target.files[0].name;
    }
  });

  editDropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    editDropArea.classList.add('border-blue-500', 'bg-blue-50');
  });

  editDropArea.addEventListener('dragleave', () => {
    editDropArea.classList.remove('border-blue-500', 'bg-blue-50');
  });

  editDropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    editDropArea.classList.remove('border-blue-500', 'bg-blue-50');

    if (e.dataTransfer.files.length > 0) {
      editPetImageInput.files = e.dataTransfer.files;
      editFileNameDisplay.textContent = e.dataTransfer.files[0].name;
    }
  });

  // Search 
  const searchInput = document.getElementById('searchInput');
  const petCards = document.querySelectorAll('.pet-card');
  const noResults = document.getElementById('noResults');
  const petContainer = document.getElementById('petContainer');

  searchInput.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    let visibleCards = 0;

    petCards.forEach(card => {
      const petName = card.getAttribute('data-pet-name');
      const species = card.getAttribute('data-species');
      const breed = card.getAttribute('data-breed');
      const sex = card.getAttribute('data-sex');

      const isMatch = petName.includes(searchTerm) || 
                     species.includes(searchTerm) || 
                     breed.includes(searchTerm) || 
                     sex.includes(searchTerm);

      if (isMatch) {
        card.classList.remove('hidden');
        visibleCards++;
      } else {
        card.classList.add('hidden');
      }
    });

    if (visibleCards === 0 && searchTerm !== '') {
      petContainer.appendChild(noResults);
      noResults.classList.remove('hidden');
    } else {
      noResults.classList.add('hidden');
    }
  });

  // Delete 
  const deleteModal = document.getElementById('deleteModal');
  const deleteForm = document.getElementById('deleteForm');
  const deletePetIdInput = document.getElementById('deletePetId');
  let currentDeleteId = null;

  function deletePet(petId) {
    currentDeleteId = petId;
    deleteModal.style.display = 'flex';
  }

  function cancelDelete() {
    deleteModal.style.display = 'none';
    currentDeleteId = null;
  }

  function confirmDelete() {
    if (currentDeleteId) {
      deletePetIdInput.value = currentDeleteId;
      deleteForm.submit();
    }
  }

  deleteModal.addEventListener('click', function(e) {
    if (e.target === deleteModal) {
      cancelDelete();
    }
  });

  editModal.addEventListener('click', function(e) {
    if (e.target === editModal) {
      toggleEditModal();
    }
  });
</script>
</body>
</html>