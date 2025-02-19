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
    <p>Go to your email to activate your account<br><?php echo htmlspecialchars($email);?><br> 
        <a href="logout.php">Back to Home</a>
    </p>
</body>
</html>
