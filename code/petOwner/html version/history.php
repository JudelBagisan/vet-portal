<?php include_once '../sql/createDB.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VetPortal</title>
  <link rel="stylesheet" href="../output.css">
<style>
      #sideBar {
        transition: all 0.5s ease;
      }

      .nav-item {
        transition: all 0.3s ease;
      }

      .nav-item:hover {
        transform: translateX(4px);
      }

      .history {
        background-color: oklch(62.3% 0.214 259.815);
        color: white;
      }
    </style>
  </head>
  <body class="min-h-screen bg-offwhite-100">
  <!-- Sidebar -->
  <?php include './nav.php'; ?>

<div class="p-10 w-full h-full flex flex-col">
  <div class="flex flex-col border-b border-b-lightgrey-100">
    <div>
      <img src="../media/burger-long.png" alt="nav-icon" class="w-10 cursor-pointer" onclick="toggleSideBar()">
    </div>
    <div class="mt-10">
        <h1 class="font-bold text-offblack-100 text-4xl">History </h1>
    </div>
    <div class="mb-5">
        <p class="text-grey-100">History of all the appointment requests and check-ins</p>
    </div>
  </div>


 <!-- Search and filters -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mt-8">
  <div class="w-full md:w-1/3 relative">
    <input type="text" placeholder="Search Pets" class="w-full px-4 py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <span class="absolute inset-y-0 right-0 flex items-center pr-3">
      <img src="../media/magnifying-glass-solid.svg" alt="search" class="w-5 h-5 text-gray-400" />
    </span>
  </div>

  <div class="flex flex-col md:flex-row gap-4 w-full md:w-auto">
    <div>
      <label class="block text-xs text-gray-500 mb-1">Status</label>
      <select class="w-full md:w-auto border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option>All Status</option>
        <option>Pending</option>
        <option>Approved</option>
        <option>Completed</option>
      </select>
    </div>

<div>
  <label class="block text-xs text-gray-500 mb-1">Date</label>
  <div class="relative">
    <input type="date" class="w-full px-4 py-2 border border-lightgrey-100 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-offblack-100 text-sm">
    <span class="absolute inset-y-0 right-3 flex focus:outline-none  items-center pointer-events-none">
    </span>
  </div>
</div>

    <div>
      <label class="block text-xs text-gray-500 mb-1">Clinic</label>
      <select class="w-full md:w-auto border border-gray-300 rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option>All Clinics</option>
        <option>Paw Clinic</option>
        <option>VetCare Center</option>
      </select>
    </div>
  </div>
</div>


<!-- Pet info -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-10">
      <div class="relative bg-white p-5 rounded-xl shadow justify-between items-start">
    <a href="pet_list.html" class="absolute top-4 right-4 flex items-center gap-1 text-blue-500 hover:underline">
         <img src="../media/eye-solid.svg" alt="QR Code" class="w-5 h-5 filter-blue">
    <span class="text-sm">View Report </span>
  </a>
        <div class="flex gap-4">
      <img src="../media/pagepic.png" alt="pet image" class="w-20 h-20 rounded-md object-cover">
          <div>
        <h2 class="font-bold text-lg">Nana</h2>
        <p class="text-sm text-gray-500"><span class="font-semibold text-gray-700">Clinic Name:</span> Paw Clinic</p>
        <p class="text-sm text-gray-500"><span class="font-semibold text-gray-700">Breed:</span> Bulldog</p>
        <p class="text-sm text-gray-500"><span class="font-semibold text-gray-700">Purpose:</span> Vaccine</p>
        <p class="text-sm text-gray-500">
          <span class="font-semibold text-gray-700">Date & Time:</span> January 1, 2023<br>10:09PM
        </p>
      </div>
    </div>
    <div class="flex justify-end mt-4">
      <div class="bg-blue-200 text-xs font-semibold px-4 py-1 rounded-full">
        Completed
      </div>
    </div>
  </div>   
       <div class="relative bg-white p-5 rounded-xl shadow justify-between items-start">
        <div class="flex gap-4">
      <img src="../media/pagepic.png" alt="pet image" class="w-20 h-20 rounded-md object-cover">
          <div>
        <h2 class="font-bold text-lg">Nana</h2>
        <p class="text-sm text-gray-500"><span class="font-semibold text-gray-700">Clinic Name:</span> Paw Clinic</p>
        <p class="text-sm text-gray-500"><span class="font-semibold text-gray-700">Breed:</span> Bulldog</p>
        <p class="text-sm text-gray-500"><span class="font-semibold text-gray-700">Purpose:</span> Vaccine</p>
        <p class="text-sm text-gray-500">
          <span class="font-semibold text-gray-700">Date & Time:</span> January 1, 2023<br>10:09PM
        </p>
      </div>
    </div>
    <div class="flex justify-end mt-4">
      <div class="bg-red-200 text-xs font-semibold px-4 py-1 rounded-full">
        Cancled
      </div>
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

