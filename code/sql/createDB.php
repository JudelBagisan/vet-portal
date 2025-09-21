<?php
$databasecreate = new mysqli('localhost', 'root', '', '', 3306);
if ($databasecreate->connect_error) {
    die("Error Executing: " . $databasecreate->connect_error);
}

$database = "CREATE DATABASE IF NOT EXISTS vetdatabase";
if (!$databasecreate->query($database)) {
    echo "ERROR CREATING DATABASE " . $databasecreate->error;
}
$databasecreate->close(); 

$conn = new mysqli('localhost', 'root', '', 'vetdatabase', 3306);
if ($conn->connect_error) {
    die("Error Executing: " . $conn->connect_error);
}

function importSQL($conn, $file, $name) {
    $sql = file_get_contents(__DIR__ . '/' . $file);
    if (!empty($sql)) {
        if (!$conn->multi_query($sql)) {
            echo "ERROR IMPORTING $name: " . $conn->error;
        }
        while ($conn->more_results() && $conn->next_result()) {;}
    }
}

importSQL($conn, 'petOwner.sql', 'petOwner.sql');
importSQL($conn, 'clinic.sql', 'clinic.sql');
importSQL($conn, 'admin.sql', 'admin.sql');
importSQL($conn, 'pets.sql', 'pets.sql');
importSQL($conn, 'petsAppointment.sql', 'petsAppointment.sql');


?>
