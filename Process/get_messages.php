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

    //if there are no messages
    if(empty($messages)){
        echo '';
        exit();
    }

    if (isset($messages["error"])) {
        echo "<p style='color: red;'>" . $messages["error"] . "</p>";
        exit();
    }

    $dateofPrevRecord='';
    foreach ($messages as $message) {
        
        // --- Date Formatting ---
        // Convert the original date string to the desired format
        $date_of_current_record = date('M j, Y', strtotime($message['message_date']));
        $formattedTime=date('g:i A', strtotime($message['message_date']));

        
        //This ensures the date is shown once for all messages that has been sent for that day
        if($dateofPrevRecord != $date_of_current_record){
            $output.='<div class="msg_date">'.$date_of_current_record.'</div>';
            $dateofPrevRecord=$date_of_current_record;
        }

            

        // --- End Date Formatting ---

        if($message['sender_username'] != $_SESSION["loggedIn_username"]){
            
            $output .= '<div class="reciever"> 
            <span class="reciever_username">' . $message['sender_username'] . '</span> 
            <span class="content">' . $message['message_content'] . '</span> 
            <span class="msg_time">' . $formattedTime. '</span>
            </div>';

        }
        else if($message['sender_username'] == $_SESSION["loggedIn_username"]){
            
            $output .= '
            <div class="sender"> 
            <span class="content">' . $message['message_content'] . '</span> 
            <span class="msg_time">' . $formattedTime. '</span>
            </div>';

        }


    }

    echo $output;
?>