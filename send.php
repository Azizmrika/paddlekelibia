<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Set CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Check request method
// if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//     http_response_code(405);
//     echo json_encode([
//         'status' => 'error',
//         'message' => 'Method Not Allowed. Only POST is accepted.'
//     ]);
//     exit;
// }

// Validate 'send' flag
if (!isset($_POST['send']) || $_POST['send'] !== '1') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

// Sanitize and validate form data
$prenom = filter_var($_POST['prenom'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$nom = filter_var($_POST['nom'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$tel = filter_var($_POST['tel'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$date = filter_var($_POST['date'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$time = filter_var($_POST['time'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$hours = filter_var($_POST['hours'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$paddle_count = filter_var($_POST['paddle-count'] ?? 1, FILTER_SANITIZE_NUMBER_INT);
$message = filter_var($_POST['message'] ?? 'Aucun message', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

if (empty($prenom) || empty($nom) || empty($tel) || empty($date) || empty($time) || empty($hours)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Tous les champs obligatoires doivent être remplis.']);
    exit;
}

if (!preg_match('/^[0-9]{8}$/', $tel)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Numéro de téléphone invalide. Veuillez entrer 8 chiffres.']);
    exit;
}

if ($paddle_count < 1 || $paddle_count > 10) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nombre de paddles invalide.']);
    exit;
}

// Prepare email
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'mrikaaziz0@gmail.com';
    $mail->Password = 'kgqlxqhrssjsfhnn'; // Use Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('mrikaaziz0@gmail.com', 'Paddle Kelibia Summer');
    $mail->addAddress('mrikaaziz0@gmail.com');
    $mail->addReplyTo('mrikaaziz0@gmail.com', 'Information');
    $mail->isHTML(true);

    // Calculate pricing
    $duration_map = [
        '1' => '1 heure',
        '2' => '2 heures',
        '3' => '3 heures',
        '4' => '4 heures',
        'full' => 'Journée complète'
    ];
    $prices = [
        '1' => 20,
        '2' => 35,
        '3' => 50,
        '4' => 70,
        'full' => 120
    ];
    $duration_text = $duration_map[$hours] ?? $hours;
    $base_price = $prices[$hours] ?? 20;
    $discount = $paddle_count >= 3 ? 0.10 : 0;
    $total_price = $base_price * $paddle_count * (1 - $discount);
    $savings = $discount > 0 ? ($base_price * $paddle_count * $discount) : 0;

    // Subject and Body
    $mail->Subject = mb_convert_encoding(
        ($_POST['subject'] ?? 'Nouvelle réservation Paddle Kelibia') . 
        " - $prenom $nom pour le $date",
        'UTF-8',
        'auto'
    );

    $mail->Body = "
        <h2>Réservation Confirmée</h2>
        <p><strong>Prénom:</strong> $prenom</p>
        <p><strong>Nom:</strong> $nom</p>
        <p><strong>Téléphone:</strong> $tel</p>
        <p><strong>Date:</strong> $date à $time</p>
        <p><strong>Durée:</strong> $duration_text</p>
        <p><strong>Nombre de paddles:</strong> $paddle_count</p>
        <p><strong>Message:</strong> $message</p>" .
        ($discount > 0 ? "<p><strong>Promotion groupe:</strong> -10%</p>" : '') .
        ($savings > 0 ? "<p><strong>Économies:</strong> " . number_format($savings, 2) . " DT</p>" : '') .
        "<p><strong>Total:</strong> " . number_format($total_price, 2) . " DT</p>
        <br>
        <p>Merci pour votre réservation ! Nous vous contacterons sous peu pour confirmer.</p>
    ";

    $mail->AltBody = "Réservation:\n" .
                     "Prénom: $prenom\n" .
                     "Nom: $nom\n" .
                     "Téléphone: $tel\n" .
                     "Date: $date à $time\n" .
                     "Durée: $duration_text\n" .
                     "Paddles: $paddle_count\n" .
                     "Message: $message\n" .
                     ($discount > 0 ? "Promotion: -10%\n" : '') .
                     ($savings > 0 ? "Économies: " . number_format($savings, 2) . " DT\n" : '') .
                     "Total: " . number_format($total_price, 2) . " DT\n" .
                     "Merci pour votre réservation !";

    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'Réservation envoyée avec succès !']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => "Erreur lors de l'envoi de l'email: " . $mail->ErrorInfo]);
}
