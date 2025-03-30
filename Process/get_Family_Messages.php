<?php
    session_start();

    require_once("../Database/database_actions.php");

    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:../logout.php");
        exit();
    }

    $senderId = $_POST['senderId'];
    $otherUsername = $POST['otherUsername'];

 

?>