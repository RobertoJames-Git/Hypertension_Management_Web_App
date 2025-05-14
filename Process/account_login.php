<?php
    require_once("sanitizeData.php");
    require_once("../Database/database_actions.php");

    session_start();

    if(!isset($_POST["account_lgin"],$_POST["username"],$_POST["password"])|| $_POST["account_lgin"]!="Login"){
        header("location:../login.php");
        exit();
    }

    # Gives user the 5 login attempts if they waited long enough
    $currentTime = microtime(true);
    if (!isset($_SESSION["login_attempts"]) || (isset($_SESSION["unlockTime"]) && $currentTime > $_SESSION["unlockTime"])) {
        $_SESSION["login_attempts"] = 5;
        unset($_SESSION["unlockTime"],$_SESSION["remainingMillis"]); // Clears unlockTime and remaining time since the user can try again
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

    

    //if there are errors then the user is redirected back to the login page
    if($valErr==true){
        header("location:../login.php");
        exit();
    }

    //only checks if user credentials is valid if they have login attempts remaining
    if($_SESSION["login_attempts"]>0){
        //if there were no validation errors then the credential will be checked using th database
        $databaseResponse=validateUser($_POST["username"],$_POST["password"]);

        if (!isset ($databaseResponse["status"])){
            /*Store error message if login fails */
            $_SESSION["dbValidate_response"]=$databaseResponse;

        }
    }

    // If credentials are successful, proceed to the next page
    if (isset($databaseResponse["status"]) && is_array($databaseResponse) && $databaseResponse["status"] === "Success") {
        // Unset data and destroy session data for current session
        session_unset();
        session_destroy();

        // Start a new session with the username
        session_start();

        // Set session variables for the logged-in user
        $_SESSION["loggedIn_username"] = $_POST["username"];

        //ise set to either 'Patient' or 'Family Member' or 'Health Care Professional'
        $_SESSION["userType"] = $databaseResponse["user_type"];


        // Redirect to the record blood pressure page
        header("location:../recordBP.php");
        exit();
    }



    if($_SESSION["login_attempts"]!=0){
        $_SESSION["login_attempts"]--;//decrement users attempts by 1
    }


    if ($_SESSION["login_attempts"] == 0) {
        // Get the current timestamp
        $currentTime = microtime(true);

        // Check if the user still needs to wait
        if (isset($_SESSION["unlockTime"]) && $currentTime < $_SESSION["unlockTime"]) {

            header("location:../login.php");
            exit();
        }

        // Calculate the unlock time (5 minutes from now)
        $waitTime = 5 * 60; // 5 minutes in seconds
        $_SESSION["unlockTime"] = $currentTime + $waitTime;

        header("location:../login.php");
        exit();
    }


    $_SESSION["dbValidate_response"].="<br>You have ".$_SESSION["login_attempts"]." attempts left.";


    header("location:../login.php");
    exit();

    
