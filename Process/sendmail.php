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
            return true;

        } catch (Exception $e) {
        return false;
            // return "Email could not be sent. Error: " . $mail->ErrorInfo;
        }
    }

    function sendActivationEmail($to){
        
        if(!isset($_SESSION['fname'],$_SESSION['lname'],$_SESSION["username"],$_SESSION['password'],$_SESSION["token"])){
            return "Not all data needed to send an email is set";
        }

        $body = "<p>
            Dear " . $_SESSION['fname'] . ' ' . $_SESSION['lname'] . ",
            <br>
            Thank you for registering with HypMonitor! We're excited to have you on board as part of our health-focused community. Whether you are a hypertensive individual, a patient, or a healthcare professional, we are here to support you on your journey.
            <br>
            To activate your account and get started, please click the link below.<br>
            The activation link is <b>only valid for 10 minutes</b>

            <br><br>
            Username: " . $_SESSION["username"] . "
            <br>Password: " . $_SESSION['password'] . "
            <br><a href='http://localhost/Major_Project_DHI/activate.php?token=" . $_SESSION["token"] . "'>Activate your account</a>
            <br><br>
            This link will verify your email and confirm that you are the one creating the account.<br>
            
            If you did not activate your account in time, don't worry! A new password and activation link will be sent to your account after you click on the expired link.
            <br><br>
            If you didn't create this account, please disregard this email.
            <br><br>
            Best regards,<br>
            The HypMonitor Team
        </p>";

        $subject="Welcome To HypMonitor! Activate your Account";
        
        $emailResult=sendMail($to,$subject,$body);

        return $emailResult;

    }
