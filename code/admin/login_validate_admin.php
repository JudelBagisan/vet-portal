<?php
require_once("../sql/createDB.php");
session_start();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = $_POST['userName'] ?? '';
    $password = $_POST['passwordAdmin'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE userName = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $adminData = $result->fetch_assoc();
            if ($adminData['password'] === $password) {
                $_SESSION['adminId'] = $adminData['adminId'];
                $_SESSION['usernameAdmin'] = $adminData['username'];

                header("Location: add_clinic.php");
                exit;
            } else {
                header("Location: admin_login.php?invalid");
                exit;
            }
        } else {
            header("Location: admin_login.php?invalid");
            exit;
        }
        
    } else {
        header("Location: admin_login.php?invalid");
        exit;
    }
}
?>
