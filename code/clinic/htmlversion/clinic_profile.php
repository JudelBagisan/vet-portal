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

      .nav-active {
        background-color: oklch(62.3% 0.214 259.815);
        color: white;
      }
    </style>
  </head>
  <body class="min-h-screen bg-offwhite-100">
  <!-- Sidebar -->
  <?php include './nav.php' ?>

<div class="p-10 w-full h-full flex flex-col">
    <div class="flex justify-between items-center">
        <img src="../media/burger-long.png" alt="nav-icon" class="w-10 cursor-pointer" onclick="toggleSideBar()">
        <button class="bg-blue-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-600 transition-colors flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
            </svg> QR Scanner
        </button>
    </div>  
</div>

  <!-- page info -->
    <div class="min-h-screen bg-gray-50 pt-4">
        <main class="px-4 sm:px-6 lg:px-8 pb-8 max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 sm:mb-8 gap-4">
                <div class="flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Clinic Information</h1>
                    <p class="text-gray-600 text-sm sm:text-base">Manage your clinic details and information</p>
                </div>
                <div class="flex justify-end sm:justify-start">
                    <button class="bg-blue-500 text-white px-4 sm:px-6 py-2 rounded-lg text-sm hover:bg-blue-600 transition-colors flex items-center gap-2 w-fit ml-auto sm:ml-0">
                        <svg class="w-4 h-4 fill-white" viewBox="0 0 512 512">
                            <path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.4-2.8 15.8-7.5 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z"/>
                        </svg>
                        <span class="hidden sm:inline">Edit Information</span>
                        <span class="sm:hidden">Edit</span>
                    </button>
                </div>
            </div>
      <!-- Basic Information  -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center gap-3">
            <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
          </div>
        </div>
        
        <div class="p-6">
          <div class="grid md:grid-cols-2 gap-8">
            <div>
              <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-2">
                Clinic Image
              </h3>
            <img src="../media/sample-clinic.png" alt="" class="rounded-md">
            </div>
            
            <!-- Clinic Details -->
            <div class="space-y-6">
              <div class="grid grid-cols-1 gap-6">
                <div>
                    Email Address
                  </label>
                  <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">davao.vetclinic@gmail.com</p>
                </div>
                
                <div>
                  <label class="text-sm font-medium text-gray-700 flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-7 0H3m2 0h5M9 7h6m-6 4h6m-6 4h6"></path>
                    </svg>
                    Clinic Name
                  </label>
                  <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">Davao Veterinary Clinic</p>
                </div>
                
                <div>
                  <label class="text-sm font-medium text-gray-700 flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Clinic Owner
                  </label>
                  <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">Dr. Maria Santos</p>
                </div>
                
                <div>
                  <label class="text-sm font-medium text-gray-700 flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Location
                  </label>
                  <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">123 Main Street, Davao City, Philippines</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Operating Schedule  -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center gap-3">
            <h2 class="text-lg font-semibold text-gray-900">Operating Schedule</h2>
          </div>
        </div>
        
        <div class="p-6">
          <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b border-gray-100">
              <span class="font-medium text-gray-700">Monday</span>
              <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">Open</span>
                <span class="text-gray-600">8:00 AM - 6:00 PM</span>
              </div>
            </div>
            
            <div class="flex items-center justify-between py-3 border-b border-gray-100">
              <span class="font-medium text-gray-700">Tuesday</span>
              <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">Open</span>
                <span class="text-gray-600">8:00 AM - 6:00 PM</span>
              </div>
            </div>
            
            <div class="flex items-center justify-between py-3 border-b border-gray-100">
              <span class="font-medium text-gray-700">Wednesday</span>
              <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">Open</span>
                <span class="text-gray-600">8:00 AM - 6:00 PM</span>
              </div>
            </div>
            
            <div class="flex items-center justify-between py-3 border-b border-gray-100">
              <span class="font-medium text-gray-700">Thursday</span>
              <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">Open</span>
                <span class="text-gray-600">8:00 AM - 6:00 PM</span>
              </div>
            </div>
            
            <div class="flex items-center justify-between py-3 border-b border-gray-100">
              <span class="font-medium text-gray-700">Friday</span>
              <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">Open</span>
                <span class="text-gray-600">8:00 AM - 6:00 PM</span>
              </div>
            </div>
            
            <div class="flex items-center justify-between py-3 border-b border-gray-100">
              <span class="font-medium text-gray-700">Saturday</span>
              <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full">Open</span>
                <span class="text-gray-600">9:00 AM - 4:00 PM</span>
              </div>
            </div>
            
            <div class="flex items-center justify-between py-3">
              <span class="font-medium text-gray-700">Sunday</span>
              <div class="flex items-center gap-3">
                <span class="px-3 py-1 bg-red-100 text-red-800 text-sm rounded-full">Closed</span>
                <span class="text-gray-400">-</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Offered Services  -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center gap-3">
            <h2 class="text-lg font-semibold text-gray-900">Offered Services</h2>
          </div>
        </div>
        
        <div class="p-6">
          <div class="flex flex-wrap gap-3">
            <span class="px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium service-tag cursor-pointer hover:bg-blue-200">
              General Checkups
            </span>
            <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full text-sm font-medium service-tag cursor-pointer hover:bg-green-200">
              Vaccinations
            </span>
            <span class="px-4 py-2 bg-purple-100 text-purple-800 rounded-full text-sm font-medium service-tag cursor-pointer hover:bg-purple-200">
              Surgery
            </span>
            <span class="px-4 py-2 bg-red-100 text-red-800 rounded-full text-sm font-medium service-tag cursor-pointer hover:bg-red-200">
              Emergency Care
            </span>
            <span class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium service-tag cursor-pointer hover:bg-yellow-200">
              Dental Care
            </span>
            <span class="px-4 py-2 bg-indigo-100 text-indigo-800 rounded-full text-sm font-medium service-tag cursor-pointer hover:bg-indigo-200">
              Grooming
            </span>
          </div>
        </div>
      </div>
    </main>
  </div>

 <script>
  const sideBar = document.querySelector('#sideBar');

  function toggleSideBar() {
      sideBar.style.transform = (sideBar.style.transform === 'translateX(-100%)' || sideBar.style.transform === '') ? 'translateX(0%)' : 'translateX(-100%)';
  }


</script>
</body>
</html>

