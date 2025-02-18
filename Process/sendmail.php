<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



require 'PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/PHPMailer-master/src/SMTP.php';


function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'dbsocial0@gmail.com'; // SMTP username
        $mail->Password = 'ckvzlzuuppaowqin'; // SMTP password
        $mail->SMTPSecure = 'ssl'; 
        $mail->Port = 465; // SMTP port
        
        // Email Headers
        $mail->setFrom('dbsocial0@gmail.com', 'HypMonitor'); // Sender
        $mail->addAddress($to); // Recipient
        $mail->addReplyTo('dbsocial@gmail.com', 'HypMonitor');
        
        // Email Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        // Send Email
        $mail->send();
        return "Email has been sent successfully.";
    } catch (Exception $e) {
        return "Email could not be sent. Error: " . $mail->ErrorInfo;
    }
}

// Example Usage
// echo sendMail('recipient@example.com', 'Test Email', '<h1>Hello!</h1><p>This is a test email.</p>');
