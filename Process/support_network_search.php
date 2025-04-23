<?php

    session_start();

    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:../logout.php");
        exit();
    }
    
    header("Content-Type: application/json");

    // Get the database connection
    require_once "../Database/database_actions.php";

    //get user type and username from get request
    $type = $_GET['type'] ?? '';
    $username = $_GET['username'] ?? '';

    //check if either is empty
    if (empty($type) || empty($username)) {
        echo json_encode([['success' => false, 'message' => 'Username and Type is required']]);
        exit;
    }

    $acceptedUserTypes = ['Family member', 'Health Care Professional', 'Patient'];

    if(!in_array($type,$acceptedUserTypes)){
        echo json_encode([['success' => false, 'message' => 'Invalid User Type. Must be "Family member" or "Health Care Professional" or "Patient"']]);
        exit;   
    }

    try {


        // Call the PHP function that interacts with the stored procedure and retrive any matches
        $matchingUsers = getMatchingUsers($username, $type,$_SESSION["loggedIn_username"]);

        // Return the results as JSON
        echo json_encode($matchingUsers);
    } catch (Exception $e) {
        // Handle errors (log them and return an empty result)
        error_log($e->getMessage());
        echo json_encode([]);
    }

    
