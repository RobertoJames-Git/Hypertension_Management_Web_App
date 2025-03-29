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

    
    if(empty($senderId)||empty($recipientId)||empty($message)){
        echo json_encode(["status" => "error", "Parameter" => "Insufficent parameter"]);
        exit();
    }


    
    if (storeChatMessage($senderId, $recipientId, $message)) {
        echo json_encode(["status" => "success", "message" => "Message sent successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error sending message"]);
    }

    exit();




?>