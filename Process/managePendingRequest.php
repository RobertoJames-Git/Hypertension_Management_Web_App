<?php

    session_start();

    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:../logout.php");
        exit();
    }

    require_once('../Database/database_actions.php');


    $sender = $_GET['sender'] ?? '';
    $decision = $_GET['decision'] ?? '';
    $loggedInUser= $_SESSION["loggedIn_username"];
    $decisionOptions = ['accepted', 'rejected'];
     
    
    if ($sender==""||$decision=="") {
        echo json_encode(['success' => false, 'message' => 'Recepient Username and decision is required.']);
        exit();

    }

    if(!in_array($decision,$decisionOptions)){
        echo json_encode(['success' => false, 'message' => 'Invalid decision. Must be "accepted" or "rejected".']);
        exit();
    }

    try {
        // Call the PHP function that corresponds to the stored procedure
        $result = managePendingRequest($sender, $loggedInUser, $decision);

        // Send success response
        if ($result['success']) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $result['error']]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

