<?php
  session_start();
  require_once("../sql/createDB.php");
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
      <div class="flex flex-col border-b border-b-lightgrey-100">
    <div class="flex justify-between items-center">
        <img src="../media/burger-long.png" alt="nav-icon" class="w-10 cursor-pointer" onclick="toggleSideBar()">
    </div>  
        <div class="mt-10">
        <h1 class="font-bold text-offblack-100 text-4xl">Appointments  Management</h1>
    </div>
    <div class="mb-5">
        <p class="text-grey-100">In this section you can view your list of appointments.</p>
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

