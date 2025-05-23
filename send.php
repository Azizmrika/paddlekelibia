<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

if (isset($_POST["send"])) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mrikaaziz0@gmail.com'; // your gmail
        $mail->Password = 'kgqlxqhrssjsfhnn'; // your gmail app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // use TLS
        $mail->Port = 587; // use port 587 for TLS
        
        // Enable debug if needed
        // $mail->SMTPDebug = 2;

        // Recipients
        $mail->setFrom('mrikaaziz0@gmail.com', 'Paddle Kelibia Summer');
        $mail->addAddress($_POST["email"]); // Add recipient
        $mail->addReplyTo('mrikaaziz0@gmail.com', 'Information');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reservation Confirmation: ' . $_POST["subject"];
        $mail->Body = '
            <h2>Reservation Confirmation</h2>
            <p><strong>Name:</strong> ' . $_POST["prenom"] . ' ' . $_POST["nom"] . '</p>
            <p><strong>Phone:</strong> ' . $_POST["tel"] . '</p>
            <p><strong>Date:</strong> ' . $_POST["date"] . ' at ' . $_POST["time"] . '</p>
            <p><strong>Duration:</strong> ' . $_POST["hours"] . ' hours</p>
            <p><strong>Number of Paddles:</strong> ' . $_POST["paddle-count"] . '</p>
            <p><strong>Message:</strong> ' . $_POST["message"] . '</p>
            <br>
            <p>Thank you for your reservation! We will contact you shortly to confirm.</p>
        ';
        $mail->AltBody = 'Reservation Details: Name: ' . $_POST["prenom"] . ' ' . $_POST["nom"] . 
                         ', Phone: ' . $_POST["tel"] . 
                         ', Date: ' . $_POST["date"] . ' at ' . $_POST["time"] . 
                         ', Duration: ' . $_POST["hours"] . ' hours' . 
                         ', Paddles: ' . $_POST["paddle-count"];
        
        $mail->send();
        
        // Also send a copy to yourself
        $mail->clearAddresses();
        $mail->addAddress('mrikaaziz0@gmail.com');
        $mail->Subject = 'New Reservation: ' . $_POST["subject"];
        $mail->send();
        
        echo "<script>
            alert('Message has been sent and reservation recorded');
            document.location.href = 'paddle.html';
        </script>";
        
    } catch (Exception $e) {
        echo "<script>
            alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');
            document.location.href = 'paddle.html';
        </script>";
    }
}
?>