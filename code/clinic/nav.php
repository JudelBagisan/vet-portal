<div class="fixed w-[60%] md:w-[18%] bg-white h-full p-6 flex flex-col text-grey-100 z-50 shadow-2xl" id="sideBar" style="transform: translateX(-100%);">
  <div class="flex flex-col">
    <div class="flex justify-end">
      <img src="../media/burger-short.png" alt="nav-icon point" class="w-10 cursor-pointer" onclick="toggleSideBar()">
    </div>
    <div class="flex mt-20">
      <div class="bg-blue-500 w-12 h-12 filter-grey rounded-md"></div>
      <div class="ml-2">
        <h1 class="font-semibold text-offblack-100 text-xl">Clinic Name</h1>
        <a href="clinic_profile.html" class="text-grey-100 cursor-pointer hover:text-blue-500">View Profile</a></div>  
    </div>
    <div class="flex flex-col gap-3 mt-20">
      <a href="dashboard.php" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer hover:text-blue-500 p-3 nav-item dashboard">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
        </svg>
        <p class="ml-2">Dashboard</p>
      </a>
      <a href="clinic_appointment.php" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer hover:text-blue-500 p-3 nav-item appointment">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
        </svg>
        <p class="ml-2">Appointment</p>
      </a>
      <a href="clinic_history.php" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer hover:text-blue-500 p-3 nav-item history">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
      </svg>
        <p class="ml-2">History</p>
      </a>
    </div>
    <a href="../logout.php" class="flex items-center rounded-md hover:bg-lightgrey-100 hover:cursor-pointer mt-20 hover:text-red-500 p-3 nav-item">
      <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
      </svg>
      <p class="ml-2">Log out</p>
    </a>
  </div>
</div>