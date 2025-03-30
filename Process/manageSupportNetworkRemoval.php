<?php
    session_start();
    require_once('../Database/database_actions.php');


    //ensure the user is logged in
    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:../logout.php");
        exit();
    }

    $supportUsername = $_GET['supportUsername'];
    $loggedInUsername = $_SESSION["loggedIn_username"];

    if($supportUsername==""){
        echo json_encode(['error' => "The username of the person that is apart fo your support network is needed."]);
    }

    try {
        // Call the PHP function to remove the connection
        $result = removeSupportNetworkConnection($loggedInUsername, $supportUsername);

        if ($result['success']) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $result['error']]);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
?>
