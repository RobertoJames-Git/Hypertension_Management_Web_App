<?php
    session_start();
    
    // Check if user is not logged in
    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:logout.php");
        exit();
    }

        
    header("Content-Type: application/json");

    // Retrieve recipient username from GET request (for AJAX)
    $username_from_search_result = $_GET['username'] ?? '';

    //the role of the user the request is being sent to
    $userType  = $_GET['type'] ?? '';

    if (empty($username_from_search_result)) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit();
    }

    if (empty($userType)) {
        echo json_encode(['success' => false, 'message' => 'User Type is required']);
        exit();
    } 

    require_once("../Database/database_actions.php");



    

    // Call the database function to populate the support table
    $result = sendRequest($_SESSION["loggedIn_username"], $username_from_search_result);


    // Check the result and send an appropriate JSON response
    if ($result === "Request Successfully processed.") {
        echo json_encode(['success' => true, 'message' => $result]);
    } else {
        echo json_encode(['success' => false, 'message' => $result]);
    }

    exit();
