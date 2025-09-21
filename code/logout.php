<?php
    require_once("./sql/createDB.php");
    session_start();

    session_unset();
    session_destroy();
    header("Location: homepage.php");
    exit();
?>