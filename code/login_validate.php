<?php
session_start();


$host = "db.kzjiizzttxpvawewpovr.supabase.co";
$dbname = "postgres";
$user = "postgres";
$port = "5432";
$password = "Judelcb1804";

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $conn = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $passwordInput = $_POST['password'] ?? '';

    if (!empty($email) && !empty($passwordInput)) {
        $stmt = $conn->prepare("SELECT * FROM pet_owner WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData) {
            if (password_verify($passwordInput, $userData['password'])) {
                $_SESSION['idLogin'] = $userData['petownerid'];
                $_SESSION['firstNameLogin'] = $userData['firstname'];
                $_SESSION['lastNameLogin'] = $userData['lastname'];
                $_SESSION['emailLogin'] = $userData['email'];
                $_SESSION['cNumber'] = $userData['cnumber'];
                $_SESSION['profilePic'] = $userData['profilepicture'];
                $_SESSION['fullName'] = $userData['firstname'] . ' ' . $userData['lastname'];

                header("Location: petOwner/pet_list.php");
                exit;
            } else {
                header("Location: login.php?invalid=1");
                exit;
            }
        } else {
            header("Location: login.php?invalid=1");
            exit;
        }
    } else {
        header("Location: login.php?invalid=1");
        exit;
    }
}
?>
