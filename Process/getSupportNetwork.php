<?php
    session_start();


    //ensure the user is logged in
    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:../logout.php");
        exit();
    }


    require_once('../Database/database_actions.php');


    $loggedInUsername = $_SESSION["loggedIn_username"];
    $result = getSupportNetwork($loggedInUsername);

    echo json_encode($result);
?>
