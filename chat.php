
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support</title>
    <link rel="stylesheet" href="styles/supportStyle.css">
    <link rel="stylesheet" href="styles/chatStyle.css">
    <script src="Javascript/supportNetwork.js"></script>
</head>
<body>
    
    <?php

        //session start already takes place in navbar.php
        require_once('navbar.php');
        require_once("Database/database_actions.php");

        if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
            header("Location:logout.php");
            exit();
        }
        
        $acceptedUserType=["Health Care Professional","Family Member","Patient"];
        
        if(!isset($_GET["chatWith"]) || !in_array($_GET["chatWith"],$acceptedUserType) ){
            header("Location:support_network.php");
            exit();
        }

        require_once('support_net_navbar.php');
    ?>



    <div id="chat_and_input_container">

        <div id="chat_container">
            <!-- Chat message bubbles -->
            <div class="reciever">Hi, how are you?</div>
            <div class="sender">I'm great, thank you! What about you?</div>

            <!-- Chat message bubbles -->
            <div class="reciever">Hi, how are you?</div>
            <div class="sender">I'm great, thank you! What about you?</div>
            
            <!-- Chat message bubbles -->
            <div class="reciever">Hi, how are you?</div>
            <div class="sender">I'm great, thank you! What about you?</div>
            
            <!-- Chat message bubbles -->
            <div class="reciever">Hi, how are you?</div>
            <div class="sender">I'm great, thank you! What about you?</div>
            
            <!-- Chat message bubbles -->
            <div class="reciever">Hi, how are you?</div>
            <div class="sender">I'm great, thank you! What about you?</div>


        </div>

        <!-- Fixed Input Box and Send Button -->
        <div id="chat_input_container">
            <textarea id="chat_textarea" placeholder="Type your message..." rows="2"></textarea>
            <button id="send_button">Send</button>
        </div>

    </div>


    

</body>


</html>