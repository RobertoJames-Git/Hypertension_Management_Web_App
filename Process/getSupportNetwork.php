<?php
    session_start();


    //ensure the user is logged in
    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:logout.php");
        exit();
    }


    require_once('../Database/database_actions.php');

    if (!isset($_SESSION["loggedIn_username"])) {
        echo json_encode(['error' => 'User not logged in.']);
        exit();
    }

    $loggedInUsername = $_SESSION["loggedIn_username"];
    $result = getSupportNetwork($loggedInUsername);

    echo json_encode($result);
?>
