<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';
session_start();

$mail = new PHPMailer(true);

$otpCode = $_SESSION['otp'];
$email   = $_SESSION['email'];

$subject = "VetSystem Account Verification";
$message = "<h3>VETSYSTEM verification link</h3>
            <p><b>Please click the link below to verify your account</b></p>
            <br>
            <a style='color:green; text-decoration:none; font-weight:600;' 
               href='http://localhost/vetportal/code/verify_email.php?tokencode=$otpCode'>Verify Email</a>";

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp-relay.brevo.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'bagisanjudel@gmail.com';
    $mail->Password   = 'jhEIqFS6gPKywHL9';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('bagisanjudel@gmail.com', 'Veterinary System');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body    = $message;
    $mail->AltBody = strip_tags($message);

    $mail->send();
    header("Location: success_email.php");
    exit;
} catch(Exception $e) {
    echo "❌ Mail Error: {$mail->ErrorInfo}";
}
?>
