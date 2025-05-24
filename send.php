<?php
// Enable CORS and return JSON
header("Access-Control-Allow-Origin: https://paddlekelibia.tn"); // Replace with your domain
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept");
header("Content-Type: application/json");

require 'vendor/autoload.php'; // Ensure PHPMailer is installed via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
    exit;
}

try {
    // Validate and sanitize inputs
    $required_fields = ['prenom', 'nom', 'tel', 'date', 'time', 'hours', 'paddle-count'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Missing or empty required field: $field");
        }
    }

    $prenom = filter_var($_POST['prenom'], FILTER_SANITIZE_STRING);
    $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
    $tel = filter_var($_POST['tel'], FILTER_SANITIZE_STRING);
    $date = filter_var($_POST['date'], FILTER_SANITIZE_STRING);
    $time = filter_var($_POST['time'], FILTER_SANITIZE_STRING);
    $hours = filter_var($_POST['hours'], FILTER_SANITIZE_STRING);
    $paddleCount = filter_var($_POST['paddle-count'], FILTER_SANITIZE_NUMBER_INT);
    $message = filter_var($_POST['message'] ?? 'Aucun message.', FILTER_SANITIZE_STRING);

    // Additional validation
    if (!preg_match("/^[0-9]{8}$/", $tel)) {
        throw new Exception("Invalid phone number format. Must be 8 digits.");
    }
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
        throw new Exception("Invalid date format.");
    }
    if (!preg_match("/^\d{2}:\d{2}$/", $time)) {
        throw new Exception("Invalid time format.");
    }
    if (!in_array($hours, ['1', '2', '3', '4', 'full'])) {
        throw new Exception("Invalid duration selected.");
    }
    if ($paddleCount < 1 || $paddleCount > 10) {
        throw new Exception("Paddle count must be between 1 and 10.");
    }

    // Convert hours to readable text
    $durations = [
        "1" => "1 heure",
        "2" => "2 heures",
        "3" => "3 heures",
        "4" => "4 heures",
        "full" => "Journée complète",
    ];
    $durationText = $durations[$hours] ?? "Durée inconnue";

    // Calculate price
    $prices = [
        "1" => 20,
        "2" => 35,
        "3" => 50,
        "4" => 70,
        "full" => 120,
    ];
    $basePrice = $prices[$hours] ?? 0;
    $discount = $paddleCount >= 3 ? 0.1 : 0; // 10% discount for 3+ paddles
    $totalPrice = $basePrice * $paddleCount * (1 - $discount);

    // Create email body
    $emailBody = "
        <h3>Nouvelle Réservation Paddle Kelibia</h3>
        <p><strong>Nom:</strong> $prenom $nom</p>
        <p><strong>Téléphone:</strong> $tel</p>
        <p><strong>Date:</strong> $date à $time</p>
        <p><strong>Durée:</strong> $durationText</p>
        <p><strong>Nombre de paddles:</strong> $paddleCount</p>
        <p><strong>Message:</strong> $message</p>
        <p><strong>Prix total:</strong> $totalPrice DT</p>
        " . ($discount > 0 ? "<p><strong>Remise groupe (10%):</strong> Appliquée</p>" : "");

    // Initialize PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'mrikaaziz0@gmail.com';
    $mail->Password = 'kgqlxqhrssjsfhnn'; // Use environment variable in production
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('mrikaaziz0@gmail.com', 'Paddle Kelibia');
    $mail->addAddress('mrikaaziz0@gmail.com', 'Admin');

    $mail->isHTML(true);
    $mail->Subject = 'Nouvelle réservation paddle';
    $mail->Body = $emailBody;

    $mail->send();
    echo json_encode(["status" => "success", "totalPrice" => $totalPrice]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Erreur: " . $e->getMessage(),
    ]);
}