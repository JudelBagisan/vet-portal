<?php

session_start();
require_once("../sql/createDB.php");

if (!isset($_SESSION['idLogin'])) {
    header("Location: ../homepage.php?noLogin");
    exit;
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
      #sideBar {
        transition: all 0.5s ease;
      }

      .nav-item {
        transition: all 0.3s ease;
      }

      .nav-item:hover {
        transform: translateX(4px);
      }

      .clinic {
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
      <h1 class="font-bold text-offblack-100 text-4xl">Welcome to Pet List</h1>
    </div>
    <div class="mb-5">
      <p class="text-grey-100">In this section you can add your pet so that you can appoint faster</p>
    </div>
  </div>

 <!-- dashcard -->

 <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-8">
      <div class="flex justify-between items-center bg-white p-6 rounded-xl shadow transform transition-all duration-300 ease-in-out hover:-translate-y-2 hover:shadow-lg cursor-pointer">
        <div>
          <p class="text-grey-100">Pets Added</p>
          <h2 class="text-2xl text-blue-500 font-bold">6</h2>
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


    <!-- interactive map -->
    <div class="flex flex-col gap-5 h-full">
    <div class="w-full h-full mt-8 gap-5 flex">
      <div class="flex gap-5 md:flex-row flex-col w-full h-full">
        <div class="md:w-[60%] w-full h-max rounded-md shadow p-6">
        <p class="font-bold text-offblack-100 text-xl">Interactive Map</p>
        <img src="../media/sample-clinic.png" alt="" class="mt-2">
        </div>


        <div class="md:w-[40%] w-full h-max shadow rounded-md p-6">
          <p class="font-bold text-offblack-100 text-xl">Clinic Description</p>
          <img src="../media/sample-vet-1.png" alt="" class="mt-2 rounded-md">
          <p class="font-semibold text-lg mt-2 text-offblack-100">Fishcher Veterinary Clinic</p>
          <div class="flex mt-2 text-grey-100 text-sm items-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
            <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
          </svg>
          <p>Ecoland Drive, Matina Davao City</p>
        </div>
        <div class="flex text-grey-100 mt-1 text-sm items-center">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
          <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
          </svg>
          <p>Monday to Friday 8:00AM - 10:00PM</p>
        </div>
        <div class="flex gap-2 mt-5 text-xs">
          <div class="bg-blue-200 p-2 rounded-xl mt-2 text-blue-500">Affordable</div>
          <div class="bg-green-100 p-2 rounded-xl mt-2 text-green-500">Easy Access</div>
        </div>
        <div>
          <button class="bg-blue-500 text-white font-semibold rounded-md p-2 w-full mt-2">Set Appointment</button>
        </div>
        </div>
      </div>
      
    </div>

    <!-- all vet -->
    <div class="flex flex-col">
    <div class="text-xl font-bold text-offblack-100 mb-2">All Veterinary Clinics</div>
    <div class="grid gap-5 md:grid-cols-3 grid-cols-1 g w-full h-full">
        <div class="w-full h-max shadow rounded-md p-6">
          <img src="../media/sample-vet-2.png" alt="" class="rounded-md">
          <p class="font-semibold text-lg mt-2 text-offblack-100">North Hoffman Veterinary Clinic</p>
          <div class="flex mt-2 text-grey-100 text-sm items-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
            <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
          </svg>
          <p>Lubogan, Toril Davao City</p>
        </div>
        <div class="flex text-grey-100 mt-1 text-sm items-center">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
          <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
          </svg>
          <p>Monday to Friday 8:00AM - 10:00PM</p>
        </div>
        <div class="flex gap-2 mt-5 text-xs">
          <div class="bg-blue-200 p-2 rounded-xl mt-2 text-blue-500">Affordable</div>
          <div class="bg-green-100 p-2 rounded-xl mt-2 text-green-500">Easy Access</div>
        </div>
        <div>
          <button class="bg-blue-500 text-white font-semibold rounded-md p-2 w-full mt-2">Set Appointment</button>
        </div>
        </div>

        <div class="w-full h-max shadow rounded-md p-6">
          <img src="../media/sample-vet-3.png" alt="" class="rounded-md">
          <p class="font-semibold text-lg mt-2 text-offblack-100">Lynwood Veterinary Clinic</p>
          <div class="flex mt-2 text-grey-100 text-sm items-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
            <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
          </svg>
          <p>Mangga St, Matina Davao City</p>
        </div>
        <div class="flex text-grey-100 mt-1 text-sm items-center">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
          <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
          </svg>
          <p>Wednesday to Friday 8:00AM - 10:00PM</p>
        </div>
        <div class="flex gap-2 mt-5 text-xs">
          <div class="bg-blue-200 p-2 rounded-xl mt-2 text-blue-500">Affordable</div>
          <div class="bg-green-100 p-2 rounded-xl mt-2 text-green-500">Easy Access</div>
        </div>
        <div>
          <button class="bg-blue-500 text-white font-semibold rounded-md p-2 w-full mt-2">Set Appointment</button>
        </div>
        </div>

        <div class="w-full h-max shadow rounded-md p-6">
          <img src="../media/sample-vet-4.png" alt="" class="rounded-md">
          <p class="font-semibold text-lg mt-2 text-offblack-100">Soutside Veterinary Clinic</p>
          <div class="flex mt-2 text-grey-100 text-sm items-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
            <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
          </svg>
          <p>Quimpo Boulevard, Matina Davao City</p>
        </div>
        <div class="flex text-grey-100 mt-1 text-sm items-center">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
          <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
          </svg>
          <p>Monday to Sunday 10:00AM - 10:00PM</p>
        </div>
        <div class="flex gap-2 mt-5 text-xs">
          <div class="bg-blue-200 p-2 rounded-xl mt-2 text-blue-500">Affordable</div>
          <div class="bg-green-100 p-2 rounded-xl mt-2 text-green-500">Easy Access</div>
        </div>
        <div>
          <button class="bg-blue-500 text-white font-semibold rounded-md p-2 w-full mt-2">Set Appointment</button>
        </div>
        </div>


        <div class="w-full h-max shadow rounded-md p-6">
          <img src="../media/sample-vet-5.png" alt="" class="rounded-md">
          <p class="font-semibold text-lg mt-2 text-offblack-100">Sierra Veterinary Clinic</p>
          <div class="flex mt-2 text-grey-100 text-sm items-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
            <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
          </svg>
          <p>Ecoland Drive, Matina Davao City</p>
        </div>
        <div class="flex text-grey-100 mt-1 text-sm items-center">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
          <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
          </svg>
          <p>Monday to Friday 8:00AM - 10:00PM</p>
        </div>
        <div class="flex gap-2 mt-5 text-xs">
          <div class="bg-blue-200 p-2 rounded-xl mt-2 text-blue-500">Affordable</div>
          <div class="bg-green-100 p-2 rounded-xl mt-2 text-green-500">Easy Access</div>
        </div>
        <div>
          <button class="bg-blue-500 text-white font-semibold rounded-md p-2 w-full mt-2">Set Appointment</button>
        </div>
        </div>

        <div class="w-full h-max shadow rounded-md p-6">
          <img src="../media/sample-vet-6.png" alt="" class="rounded-md">
          <p class="font-semibold text-lg mt-2 text-offblack-100">Paws & Tails Veterinary Clinic</p>
          <div class="flex mt-2 text-grey-100 text-sm items-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
            <path fill-rule="evenodd" d="m11.54 22.351.07.04.028.016a.76.76 0 0 0 .723 0l.028-.015.071-.041a16.975 16.975 0 0 0 1.144-.742 19.58 19.58 0 0 0 2.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 0 0-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 0 0 2.682 2.282 16.975 16.975 0 0 0 1.145.742ZM12 13.5a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" clip-rule="evenodd" />
          </svg>
          <p>Ecoland Drive, Matina Davao City</p>
        </div>
        <div class="flex text-grey-100 mt-1 text-sm items-center">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-4">
          <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clip-rule="evenodd" />
          </svg>
          <p>Tuesday to Friday 8:00AM - 10:00PM</p>
        </div>
        <div class="flex gap-2 mt-5 text-xs">
          <div class="bg-blue-200 p-2 rounded-xl mt-2 text-blue-500">Affordable</div>
          <div class="bg-green-100 p-2 rounded-xl mt-2 text-green-500">Easy Access</div>
        </div>
        <div>
          <button class="bg-blue-500 text-white font-semibold rounded-md p-2 w-full mt-2">Set Appointment</button>
        </div>
        </div>

        
        </div>
      </div>
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

