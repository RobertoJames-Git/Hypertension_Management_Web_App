<?php

    session_start();
    require_once("../Database/database_actions.php");

    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:../logout.php");
        exit();
    }

    // Get data from the request
    $senderId = $_POST['senderId'] ?? '';
    $recipientId = $_POST['recipientId'] ?? '';
    $message = $_POST['message'] ?? '';
    $chatType = $_POST['chatWith'] ?? '';
    $patientUsername = $_POST["patientUsername"] ?? '';

    
    if(empty($senderId)||empty($recipientId)||empty($message)||empty($chatType)){
        echo json_encode(["status" => "error", "Parameter" => "Insufficent parameter"]);
        exit();
    }

    // If patient and health Care communication is taking place then data is retirved from a different fucntion
    if(($chatType=="Patient" && $_SESSION["userType"]!="Family Member" )||$chatType=="Health Care Professional"){
        $result=storeChatMessage($senderId, $recipientId, $message);
    }
    // If family member and patient communication is taking place then data is retirved from a different function
    else if($chatType=="Family Member"||  ($chatType=="Patient" && $_SESSION["userType"]=="Family Member" )){
        $result=addFamilyChatMessage($_SESSION["loggedIn_username"],$senderId,$message,$patientUsername,$recipientId);
    }


    if (isset($result["success"])) {
        http_response_code(200); // Success
        echo json_encode(["status" => "success", "message" => "Message sent successfully"]);
    } else if (isset($result["error"])) {
        http_response_code(400); // Client-side error
        echo json_encode(["status" => "error", "message" => "Error sending message please refresh"]);
    }
    exit();
    

?>