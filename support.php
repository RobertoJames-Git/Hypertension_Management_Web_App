
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support</title>
    <link rel="stylesheet" href="styles/supportStyle.css">
</head>
<body>
    
    <?php

        //session start already takes place in navbar.php
        require_once('navbar.php');

        if(!isset($_SESSION["loggedIn_username"])|| $_SESSION["loggedIn_username"]==""){
            header("Location:login.php");
        }
    ?>

    <div id="Support_Navbar">
        <div id="support_selected">Manage Support Network</div>
        <div>Family Chat</div>
        <div>Healthcare Professional Chat</div>
    </div>

</body>
</html>