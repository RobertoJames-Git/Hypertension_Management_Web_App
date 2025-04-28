<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;



    require 'PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
    require 'PHPMailer-master/PHPMailer-master/src/Exception.php';
    require 'PHPMailer-master/PHPMailer-master/src/SMTP.php';


    function sendMail($recipients, $subject, $body) {
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
            $mail->addReplyTo('dbsocial@gmail.com', 'HypMonitor');
            
            // Add multiple recipients
            foreach ($recipients as $recipient) {
                $mail->addAddress($recipient); // Add each recipient
            }
            
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
        
        $emailResult=sendMail(array($to),$subject,$body);

        unset($_SESSION['fname'],$_SESSION['lname'],$_SESSION["username"],$_SESSION['password'],$_SESSION["token"]);
        
        return $emailResult;

    }



    function sendAlertEmailToSupportNetword($recipients, $patient_name,$recommended_min_Systolic,$recommended_min_Diastolic,$recommended_max_Systolic,$recommended_max_Diastolic,$patient_systolic,$patient_diastolic,$patient_heartRate){

        $body="
        Greetings,<br>
        This is an automated message regarding $patient_name. Their recent health readings have fallen outside the recommended range:<br>
        <br>
        <b>Patient Readings</b><br>
        - Systolic: $patient_systolic mmHg<br>
        - Diastolic: $patient_diastolic mmHg<br>
        - Heart Rate: $patient_heartRate BPM<br>
        <br>
        <b>Recommended Readings</b><br>
        - Systolic: $recommended_min_Systolic mmHg - $recommended_max_Systolic mmHg<br>
        - Diastolic: $recommended_min_Diastolic mmHg - $recommended_max_Diastolic mmHg<br>
        <br>
        As members of $patient_name's support network, your attention is important to ensure their health and safety. If you are a family member, please check in with $patient_name's or offer support as needed. If you are $patient_name healthcare professional, we advise reviewing these readings promptly to provide guidance or intervention as required.<br>
        Thank you for your dedication to $patient_name's well-being. Together, we can ensure they receive the care and support they need.<br><br>
        Best regards,<br>
        HypMonitor Team";

        $subject ="Urgent: Patient Health Update Requires Attention";

        $sendMailResult=sendMail($recipients,$subject,$body);
        
        return $sendMailResult;
    }


