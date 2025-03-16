<?php
    session_start();

    // Check if user is not logged in
    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:logout.php");
        exit();
    }

    // Retrieve recipient username from POST request (for AJAX)
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


    //only send a Monitor request if:
    // 1) The health care professional send a request since they can only send request to patients
    // 2) The Patient is sending a request and in the drop down they select health care prof
    
    if( $_SESSION["userType"]=="Health Care Professional" || ($_SESSION["userType"]=="Patient" && $userType=="Health Care Professional") ){
        
        $result = sendMonitorRequest($_SESSION["loggedIn_username"], $username_from_search_result);

    }
    else{
        // Call the database function to populate the support table
        $result = populateSupportTable($_SESSION["loggedIn_username"], $username_from_search_result);
    }

    // Check the result and send an appropriate JSON response
    if ($result === "Support network relationship successfully added with status 'pending'."||$result ==="Monitor request processed.") {
        echo json_encode(['success' => true, 'message' => $result]);
    } else {
        echo json_encode(['success' => false, 'message' => $result]);
    }

    exit();
