<?php

    session_start();

    require_once("../Database/database_actions.php");

    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:logout.php");
        exit();
    }

    // Get sender and recipient IDs from the request
    $senderId = $_GET['senderId'];
    $recipientId = $_GET['recipientId'];

    // Fetch messages from the database
    $messages = getChatMessages($senderId, $recipientId); // Assuming this function is in database_actions.php

    // Format and return the messages
    $output = '';
    foreach ($messages as $message) {

        if($message['sender_username'] != $_SESSION["loggedIn_username"]){
            $output .= '<div class="reciever"> 
                            <span class="reciever_username">' . $message['sender_username'] . '</span> 
                            <span class="content">' . $message['message_content'] . '</span> 
                        </div>';
        }
        else if($message['sender_username'] == $_SESSION["loggedIn_username"]){
            $output .= '<div class="sender">' . $message['message_content'] . ' </div>';
        }


    }

    echo $output;
?>