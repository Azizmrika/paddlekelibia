<?php
// Enable CORS and return JSON
header("Access-Control-Allow-Origin: *"); // Or specify your domain instead of *
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept");

require 'vendor/autoload.php'; // Make sure PHPMailer is installed via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if POST request has required fields
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Accept");
    http_response_code(200);
    exit();
}
try {
    $prenom = $_POST['prenom'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $tel = $_POST['tel'] ?? '';
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $hours = $_POST['hours'] ?? '';
    $paddleCount = $_POST['paddle-count'] ?? '';
    $message = $_POST['message'] ?? 'Aucun message.';

    // Convert hours to readable text
    $durations = [
        "1" => "1 heure",
        "2" => "2 heures",
        "3" => "3 heures",
        "4" => "4 heures",
        "full" => "Journée complète",
    ];
    $durationText = $durations[$hours] ?? "Durée inconnue";

    $emailBody = "
        <h3>Nouvelle Réservation Paddle Kelibia</h3>
        <p><strong>Nom:</strong> $prenom $nom</p>
        <p><strong>Téléphone:</strong> $tel</p>
        <p><strong>Date:</strong> $date à $time</p>
        <p><strong>Durée:</strong> $durationText</p>
        <p><strong>Nombre de paddles:</strong> $paddleCount</p>
        <p><strong>Message:</strong> $message</p>
    ";

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'mrikaaziz0@gmail.com';
    $mail->Password = 'kgqlxqhrssjsfhnn'; // Ensure this is a valid Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('your-email@example.com', 'Paddle Kelibia');
    $mail->addAddress('destination@example.com', 'Admin'); // Replace with recipient

    $mail->isHTML(true);
    $mail->Subject = 'Nouvelle réservation paddle';
    $mail->Body = $emailBody;

    $mail->send();
    echo json_encode(["status" => "success"]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}",
    ]);
}
