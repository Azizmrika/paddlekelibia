<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// CORS & Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Max-Age: 86400");

// if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
//     http_response_code(200);
//     exit();
// }

// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     http_response_code(405);
//     echo json_encode(['status' => 'error', 'message' => 'Méthode non autorisée']);
//     exit();
// }

try {
    $required_fields = ['prenom', 'nom', 'tel', 'date', 'time', 'hours', 'paddle-count'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Champ requis manquant: $field");
        }
    }

    // Sanitize
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $nom = htmlspecialchars(trim($_POST['nom']));
    $tel = htmlspecialchars(trim($_POST['tel']));
    $date = htmlspecialchars(trim($_POST['date']));
    $time = htmlspecialchars(trim($_POST['time']));
    $hours = htmlspecialchars(trim($_POST['hours']));
    $paddleCount = (int)$_POST['paddle-count'];
    $message = htmlspecialchars(trim($_POST['message'] ?? 'Aucun message.'));

    // Validation
    // if (!preg_match("/^[0-9]{8}$/", $tel)) throw new Exception("Numéro de téléphone invalide.");
    // if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) throw new Exception("Format de date invalide.");
    // if (!preg_match("/^\d{2}:\d{2}$/", $time)) throw new Exception("Format d'heure invalide.");
    // if (!in_array($hours, ['1', '2', '3', '4', 'full'])) throw new Exception("Durée invalide.");
    // if ($paddleCount < 1 || $paddleCount > 10) throw new Exception("Nombre de paddles invalide.");

    $durations = [
        "1" => "1 heure",
        "2" => "2 heures",
        "3" => "3 heures",
        "4" => "4 heures",
        "full" => "Journée complète",
    ];
    $prices = [
        "1" => 20,
        "2" => 35,
        "3" => 50,
        "4" => 70,
        "full" => 120,
    ];
    $durationText = $durations[$hours];
    $basePrice = $prices[$hours];
    $discount = $paddleCount >= 3 ? 0.1 : 0;
    $totalPrice = $basePrice * $paddleCount * (1 - $discount);

    $emailBody = "
        <h3>Nouvelle Réservation Paddle Kelibia</h3>
        <p><strong>Nom:</strong> $prenom $nom</p>
        <p><strong>Téléphone:</strong> $tel</p>
        <p><strong>Date:</strong> $date à $time</p>
        <p><strong>Durée:</strong> $durationText</p>
        <p><strong>Nombre de paddles:</strong> $paddleCount</p>
        <p><strong>Message:</strong> $message</p>
        <p><strong>Prix total:</strong> $totalPrice DT</p>" .
        ($discount > 0 ? "<p><strong>Remise groupe (10%):</strong> Appliquée</p>" : "");

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'mrikaaziz0@gmail.com';
    $mail->Password = 'kgqlxqhrssjsfhnn';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('mrikaaziz0@gmail.com', 'Paddle Kelibia');
    $mail->addAddress('mrikaaziz0@gmail.com');

    $mail->isHTML(true);
    $mail->Subject = 'Nouvelle réservation paddle';
    $mail->Body = $emailBody;

    $mail->send();

    echo json_encode(["status" => "success", "totalPrice" => $totalPrice]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
