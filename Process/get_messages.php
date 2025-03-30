<?php

    session_start();

    require_once("../Database/database_actions.php");

    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:../logout.php");
        exit();
    }

    // Get sender and recipient IDs from the request
    $senderId = $_GET['senderId'];
    $patientUsername = $_GET["patientUsername"];
    $recipientId = $_GET['recipientId'];
    $chatType = $_GET['chatWith'];


    // If patient and health Care communication is taking place then data is retirved from a different fucntion
    if(($chatType=="Patient" && $_SESSION["userType"]!="Family Member" ) ||$chatType=="Health Care Professional"){
        $messages = getChatMessages($senderId, $recipientId);
    }
    // If family member and patient communication is taking place then data is retirved from a different function
    else if($chatType=="Family Member" || ($chatType=="Patient" && $_SESSION["userType"]=="Family Member" )){
        $messages = getFamilyChatMessages($_SESSION["loggedIn_username"],$patientUsername); 
    }

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