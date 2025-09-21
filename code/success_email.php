<?php
session_start();

$host = "db.kzjiizzttxpvawewpovr.supabase.co";
$dbname = "postgres";
$user = "postgres";
$port = "5432";
$password = "Judelcb1804";

function randomOTP() {
    return rand(100000, 999999);
}

if (isset($_POST["signupBtn"])) {
    $firstName = $_POST['firstName'];
    $lastName  = $_POST['lastName'];
    $passwordI = $_POST['password'];
    $email     = $_POST['email'];
    $cNumber   = $_POST['cNumber'];
    $otp       = randomOTP();

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
        $conn = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $hashedPassword = password_hash($passwordI, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO pet_owner 
        (firstname, lastname, fullname, password, email, cnumber, otp, is_verified) 
        VALUES (:fname, :lname, :fullname, :pword, :email, :cnum, :otp, false)");

        $stmt->execute([
            ':fname'    => $firstName,
            ':lname'    => $lastName,
            ':fullname' => $firstName . ' ' . $lastName,
            ':pword'    => $hashedPassword,
            ':email'    => $email,
            ':cnum'     => $cNumber,
            ':otp'      => $otp
        ]);


        $_SESSION['otp']   = $otp;
        $_SESSION['email'] = $email;

        header("Location: send_email.php");
        exit;
    } catch (PDOException $e) {
        die("❌ Registration failed: " . $e->getMessage());
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in</title>
    <link rel="stylesheet" href="./output.css">
</head>
<body class="bg-gradient-to-tl from-offwhite-100 to-white-100">
    <div class="absolute top-0 left-0 mt-10 ml-10 font-bold text-white text-xl">
        VETSYSTEM
    </div>
    <div class="bg-gradient-to-tl from-secondary-100 to-primary-100 flex flex-col items-center justify-center box-border w-screen h-screen">
        <div class="sm:w-[35vw] sm:h-[40vh] bg-white flex flex-col items-center justify-center border-lightgrey-100 border rounded-md">
            <img src="./media/circle-check-regular.svg" alt="Check" class="w-20 h-20 filter-green"><br>
            <h1 class="font-semibold text-2xl text-green-500">Successful</h1><br>
            <p class="text-grey-100">A verification link has been sent to your email.</p>
            <button class="bg-blue-500 text-white font-semibold mt-2 rounded-sm sm:w-[8vw] p-2 w-[30vw] hover:bg-blue-600"><a href="homepage.php">OK</a></button>
        </div>
    </div>
    <?php include 'chatbot.php' ?>
</body>
</html>