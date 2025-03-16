<?php
    session_start();

    // Check if user is not logged in
    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:logout.php");
        exit();
    }

    // Retrieve recipient username from POST request (for AJAX)
    $family_mem_username = $_GET['username'] ?? '';

    if (empty($family_mem_username)) {
        echo json_encode(['success' => false, 'message' => 'Username is required']);
        exit();
    }

    require_once("../Database/database_actions.php");



    if($_SESSION["userType"]=="Health Care Professional"){

        $result = sendMonitorRequest($_SESSION["loggedIn_username"], $family_mem_username);

    }
    else{
        // Call the database function to populate the support table
        $result = populateSupportTable($_SESSION["loggedIn_username"], $family_mem_username);
    }
    // Check the result and send an appropriate JSON response
    if ($result === "Support network relationship successfully added with status 'pending'."||$result ==="Monitor request processed.") {
        echo json_encode(['success' => true, 'message' => $result]);
    } else {
        echo json_encode(['success' => false, 'message' => $result]);
    }

    exit();
