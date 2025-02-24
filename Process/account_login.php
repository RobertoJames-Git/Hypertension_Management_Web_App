<?php
    require_once("sanitizeData.php");
    require_once("../Database/database_actions.php");

    session_start();

    if(!isset($_POST["account_lgin"],$_POST["username"],$_POST["password"])|| $_POST["account_lgin"]!="Login"){
        header("location:../login.php");
        exit();
    }

    unset($_SESSION["usernameErr"],$_SESSION["passwordErr"],$_SESSION["dbValidate_response"]);
    $valErr=false;

    //sanitize all user input
    $_POST = sanitizeUserInput($_POST);

    #stores the users input in a session to make their info persistent in the form
    $_SESSION["username"]=$_POST["username"];


    //check if field is empty
    if($_POST["username"]==""){
        $_SESSION["usernameErr"]="Field is empty";
        $valErr=true;
    }
    //check if the username is valid
    else if (!preg_match('/^[A-Za-z]{2,3}_[A-Za-z]{2,3}[0-9]+$/', $_POST["username"])) {
        $_SESSION["usernameErr"] = "Invalid username";
        $valErr = true; 
    }

    //check if field is empty
    if($_POST["password"]==""){
        $_SESSION["passwordErr"]="Field is empty";
        $valErr=true;
    }
    //check if password is less than 8 characters long
    else if(strlen($_POST["password"])<8){
        $_SESSION["passwordErr"]="8 or more characters needed";
        $valErr=true;
    }

    //if there are errors then the user is redirected back to the login page
    if($valErr==true){
        header("location:../login.php");
        exit();
    }

    //if there were no validation errors then then the credential will be checked using th database
    $databaseResponse=validateUser($_POST["username"],$_POST["password"]);


    //if credentials are successfult then go to recordBP page
    if($databaseResponse==="Success"){
        unset($_SESSION["username"],$_SESSION["usernameErr"],$_SESSION["passwordErr"],$_SESSION["dbValidate_response"]);
        header("location:../recordBP.php");
        exit();
    }

    /*Store error message and redirect if login fails */
    $_SESSION["dbValidate_response"]=$databaseResponse;
    header("location:../login.php");
    exit();

    
