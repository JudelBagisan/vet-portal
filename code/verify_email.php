<?php
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

if (isset($_GET["tokencode"])) {
    $emailtoken = (int)$_GET['tokencode'];

    $stmt = $conn->prepare("SELECT * FROM pet_owner WHERE otp = :otp AND is_verified = false LIMIT 1");
    $stmt->execute([':otp' => $emailtoken]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $update = $conn->prepare("UPDATE pet_owner SET is_verified = true, otp = NULL WHERE petownerid = :id");
        $update->execute([':id' => $user['petownerid']]);

        header("Location: verify_email.php?success");
        exit;
    } else {
        header("Location: verify_email.php?unsuccessful");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Email Verification</title>
  <link rel="stylesheet" href="./output.css">
</head>
<body>
  <div class="bg-gradient-to-tl from-secondary-100 to-primary-100 flex flex-col items-center justify-center box-border w-screen h-screen">
    <div class="sm:w-[35vw] sm:h-[40vh] bg-white flex flex-col items-center justify-center border-lightgrey-100 border rounded-md">
      <?php if(isset($_GET['success'])) { ?>
        <img src="./media/circle-check-regular.svg" alt="Check" class="w-20 h-20 filter-green"><br>
        <h1 class="font-semibold text-2xl text-green-500">Successful</h1><br>
        <p class="text-grey-100">Email verified successfully! You can now log in.</p>
        <button class="cursor-pointer bg-blue-500 hover:bg-blue-600 text-white font-semibold mt-2 rounded-sm sm:w-[10vw] p-2 w-[30vw]">
          <a href="login.php">Proceed to sign in!</a>
        </button>
      <?php } ?>

      <?php if(isset($_GET['unsuccessful'])) { ?>
        <img src="./media/circle-xmark-regular.svg" alt="Check" class="w-20 h-20 filter-red"><br>
        <h1 class="font-semibold text-2xl text-red-500">Unsuccessful</h1><br>
        <p class="text-grey-100">Email link expired or invalid!</p>
        <button class="bg-blue-500 text-white font-semibold mt-2 rounded-sm sm:w-[10vw] p-2 w-[30vw]">
          <a href="homepage.php">Back to home</a>
        </button>
      <?php } ?>
    </div>
  </div>
</body>
</html>
