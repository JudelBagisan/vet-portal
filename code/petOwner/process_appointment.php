<?php
require_once("../sql/createDB.php");
session_start();

if (!isset($_SESSION['idLogin'])) {
    header("Location: ../homepage.php?noLogin");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $petOwnerId = $_SESSION['idLogin'];
    $clinicID = $_POST['clinicID'] ?? '';
    $petId = $_POST['petId'] ?? '';
    $appointmentDate = $_POST['appointmentDate'] ?? '';
    $timeSlot = $_POST['timeSlot'] ?? '';
    $purpose = $_POST['purpose'] ?? '';
    
    if (empty($clinicID) || empty($petId) || empty($appointmentDate) || empty($timeSlot) || empty($purpose)) {
        $_SESSION['error_message'] = "All fields are required. Please fill in all the information.";
        header("Location: add_appointment.php");
        exit;
    }
    
    try {
        $petSql = "SELECT petImage_url FROM pets WHERE petId = ? AND petOwnerId = ?";
        $petStmt = $conn->prepare($petSql);
        $petStmt->bind_param("ii", $petId, $petOwnerId);
        $petStmt->execute();
        $petResult = $petStmt->get_result();
        
        if ($petResult->num_rows === 0) {
            $_SESSION['error_message'] = "Selected pet not found or doesn't belong to you.";
            header("Location: add_appointment.php");
            exit;
        }
        
        $pet = $petResult->fetch_assoc();
        
        $clinicSql = "SELECT clinicName FROM clinic WHERE clinicID = ?";
        $clinicStmt = $conn->prepare($clinicSql);
        $clinicStmt->bind_param("i", $clinicID);
        $clinicStmt->execute();
        $clinicResult = $clinicStmt->get_result();
        
        if ($clinicResult->num_rows === 0) {
            $_SESSION['error_message'] = "Selected clinic not found.";
            header("Location: add_appointment.php");
            exit;
        }
        
        $clinic = $clinicResult->fetch_assoc();
        
        $timeSlotParts = explode('-', $timeSlot);
        if (count($timeSlotParts) !== 2) {
            $_SESSION['error_message'] = "Invalid time slot format.";
            header("Location: add_appointment.php");
            exit;
        }
        
        $appointTime = $timeSlotParts[0]; 
        
        $checkSql = "SELECT appointmentId FROM petsappointment 
                     WHERE petId = ? AND appointDate = ? AND appointTime = ? 
                     AND appointment_status NOT IN ('rejected', 'cancelled')";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("iss", $petId, $appointmentDate, $appointTime);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $_SESSION['error_message'] = "An appointment already exists for this pet at the selected date and time.";
            header("Location: add_appointment.php");
            exit;
        }
        
$insertSql = "INSERT INTO petsappointment 
              (petOwnerId, petId, clinicId, appointDate, appointTime, purpose, appointment_status) 
              VALUES (?, ?, ?, ?, ?, ?, 'pending')";


$insertStmt = $conn->prepare($insertSql);
$insertStmt->bind_param("iiisss", 
    $petOwnerId, 
    $petId, 
    $clinicID, 
    $appointmentDate, 
    $appointTime, 
    $purpose
);

if ($insertStmt->execute()) {
    $_SESSION['success_message'] = "Appointment successfully scheduled! Please wait for clinic confirmation.";
} else {
    $_SESSION['error_message'] = "Failed to schedule appointment. Please try again.";
}

    } catch (Exception $e) {
        $_SESSION['error_message'] = "An error occurred while processing your appointment: " . $e->getMessage();
    }
    
    header("Location: add_appointment.php");
    exit;
} else {
    header("Location: add_appointment.php");
    exit;
}
?>