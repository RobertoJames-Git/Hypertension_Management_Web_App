<?php
    session_start();

    // Redirect if session variables are not set
    if (!isset($_SESSION["email_status"], $_SESSION["email"])) {
        header("location:index.php");
    }

    $email = $_SESSION["email"];
    
    //destroy session after creation so that the user cannot come back to this page
    session_unset();
    session_destroy();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Email</title>
</head>
<body>

    <style>
        #emailAlertContainer{
            width:547px;
            text-align: center;
                
            /* Centering */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0px 0px 8px 2px black;
        }


        body{
            font-family: sans-serif;
            background-color: rgb(230, 230, 240);
            color: black;
        }  
        #emailAlertMessage{
            background-color: white;
            width: max-content;
            padding:10px;
        } 

        #backtohome_link{
            background-color: #1946da;
            cursor: pointer;
            color: white;
            padding: 10px;
            
        }


    </style>

    <div id="emailAlertContainer">
        <div id="emailAlertMessage">

        <img src="images/emailAlertImages/email.png" alt="" width="100">
            <h2>Check your email</h2>
            <p>You entered <b><?php echo htmlspecialchars($email);?> </b>as the email address for your account<br>
                Go to your email to get your account credentials and activate your account<br> 
            </p>
        </div>

        <div id="backtohome_link" onclick="window.location.href='logout.php'">Back to Home</div>
    </div>
</body>
</html>
