<?php
require_once("sql/createDB.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = $_POST['emailClinic'] ?? '';
    $password = $_POST['passwordClinic'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM clinic WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $clinicData = $result->fetch_assoc();
            if ($clinicData['password'] === $password) {
                $_SESSION['idLogin'] = $clinicData['clinicId'];
                $_SESSION['clinicNameLogin'] = $clinicData['clinicName'];
                $_SESSION['clinicOwnerLogin'] = $clinicData['clinicOwner'];
                $_SESSION['emailLogin'] = $clinicData['email'];
                $_SESSION['cNumber'] = $clinicData['cNumber'];
                $_SESSION['offeredServices'] = $clinicData['offeredServices'];
                $_SESSION['clinicAddress'] = $clinicData['clinicAddress'];
                $_SESSION['proof'] = $clinicData['proof'];
                $_SESSION['clinicPhoto'] = $clinicData['clinicPhoto'];
                $_SESSION['created_at'] = $clinicData['created_at'];

                header("Location: clinic/clinic_profile.php");
                exit;
            } else {
                header("Location: clinic_login.php?invalid");
                exit;
            }
        } else {
            header("Location: clinic_login.php?invalid");
            exit;
        }
    } else {
        header("Location: clinic_login.php?invalid");
        exit;
    }
}
?>
