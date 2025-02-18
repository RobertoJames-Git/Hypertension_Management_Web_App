<?php

    session_start();
    if(!isset($_SESSION["email_status"])){
        header("location:index.php");
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Email</title>
</head>
<body>
    <p>Go to your email to activate your account<br><?php echo  $_SESSION["email"] ?></p>
</body>
</html>
