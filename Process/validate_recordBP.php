<?php

    session_start();
    require_once("sanitizeData.php");
    require_once("../Database/database_actions.php"); // Include the file where addBloodPressureReading() is defined

    // Check if user is still logged in
    if (!isset($_SESSION["loggedIn_username"])) {
        header("location:../login.php");
        exit(); // Remaining code not executed
    }

    // Check if all required POST variables are set
    if (!isset($_POST["bp_record"], $_POST["systolic"], $_POST["diastolic"], $_POST["heart_rate"], $_POST["date"])) {
        header("location:../recordBP.php");
        exit(); // Remaining code not executed
    }

    // Initialize error session variables
    $_SESSION["dbMessage"]=$_SESSION["systolicErr"] = $_SESSION["diastolicErr"] = $_SESSION["heart_rateErr"]  = $_SESSION["dateErr"] = "";

    // Flag to track validation errors
    $valErr = false;

    // Sanitize user input
    $_POST = sanitizeUserInput($_POST);

    // Validate Systolic Blood Pressure
    if ($_POST["systolic"] == "") {
        $_SESSION["systolicErr"] = "Field is empty";
        $valErr = true;
    } elseif (!ctype_digit($_POST["systolic"])) { // Ensures only numeric characters
        $_SESSION["systolicErr"] = "Numbers Only";
        $valErr = true;
    }
    elseif(intval($_POST["systolic"])<50 ||intval($_POST["systolic"])>300){
        $_SESSION["systolicErr"] = "Accepted Range 50 - 300";
        $valErr = true;
    }

    // Validate Diastolic Blood Pressure
    if ($_POST["diastolic"] == "") {
        $_SESSION["diastolicErr"] = "Field is empty";
        $valErr = true;
    } elseif (!ctype_digit($_POST["diastolic"])) {
        $_SESSION["diastolicErr"] = "Numbers Only";
        $valErr = true;
    }
    elseif(intval($_POST["diastolic"])<30 ||intval($_POST["diastolic"])>200){
        $_SESSION["diastolicErr"] = "Accepted Range 30 - 200";
        $valErr = true;
    }

    // Validate Heart Rate
    if ($_POST["heart_rate"] == "") {
        $_SESSION["heart_rateErr"] = "Field is empty";
        $valErr = true;
    } elseif (!ctype_digit($_POST["heart_rate"])) {
        $_SESSION["heart_rateErr"] = "Numbers Only";
        $valErr = true;
    }
    elseif(intval($_POST["heart_rate"])<50 ||intval($_POST["heart_rate"])>300){
        $_SESSION["heart_rateErr"] = "Accepted Range 50 - 300";
        $valErr = true;
    }

    // Set the timezone to Jamaica
    date_default_timezone_set("America/Jamaica");


    // Validate Date

    if ($_POST["date"] == "") {
        $_SESSION["dateErr"] = "Field is empty";
        $valErr = true;
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $_POST["date"])) {
        $_SESSION["dateErr"] = "Invalid date format";
        $valErr = true;
    } elseif (strtotime($_POST["date"]) > strtotime(date("Y-m-d"))) {
        $_SESSION["dateErr"] = "Date cannot be in the future";
        $valErr = true;
    }


    // Keep user input persistent in form
    $_SESSION["systolic"] = $_POST["systolic"];
    $_SESSION["diastolic"] = $_POST["diastolic"];
    $_SESSION["heart_rate"] = $_POST["heart_rate"];
    $_SESSION["date"] = $_POST["date"];

    // If no validation errors, proceed to insert data into the database
    if (!$valErr) {
        $username = $_SESSION["loggedIn_username"];
        $readingDate = $_POST["date"];
        $readingTime = date("H:i:s");//get corrent time in the format HH:MM:SS (24-hour format).
        $systolic = (int)$_POST["systolic"];
        $diastolic = (int)$_POST["diastolic"];
        $heartRate = (int)$_POST["heart_rate"];

        // Call the function to add blood pressure reading
        $result = addBloodPressureReading($username, $readingDate, $readingTime, $systolic, $diastolic, $heartRate);

        
        // Handle result
        if ($result===true) {
            
            //unset all variables 
            session_unset(); 
            $_SESSION["loggedIn_username"] = $username; // Restore username
            
            $_SESSION["dbMessage"] = "Record Added Successfully";
            header("location:../recordBP.php");
            exit();
        } else {
            $_SESSION["dbMessage"] = $result; // Store database error message
            header("location:../recordBP.php");
            exit();
        }
    } else {

        // Redirect back if validation fails
        header("location:../recordBP.php");
        exit();
    }
