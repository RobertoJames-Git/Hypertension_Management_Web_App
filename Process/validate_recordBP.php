<?php

    session_start();

    
    //ensure the user is logged in
    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:../logout.php");
        exit();
    }

    // Check if all required POST variables are set
    if (!isset($_POST["bp_record"], $_POST["systolic"], $_POST["diastolic"], $_POST["heart_rate"], $_POST["date"])) {
        header("location:../recordBP.php");
        exit(); // Remaining code not executed
    }

    require_once("sanitizeData.php");
    require_once("../Database/database_actions.php"); // Include the file where addBloodPressureReading() is defined

    // Initialize error session variables
    $_SESSION["patientRange_Err"]=$_SESSION["dbMessage"]=$_SESSION["systolicErr"] = $_SESSION["diastolicErr"] = $_SESSION["heart_rateErr"]  = $_SESSION["dateErr"]=$_SESSION["timeErr"] = "";

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
    elseif(intval($_POST["systolic"])<50 ||intval($_POST["systolic"])>400){
        $_SESSION["systolicErr"] = "Accepted Range 50 - 400";
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


    // Validate Time
    if ($_POST["time"] == "") {
        $_SESSION["timeErr"] = "Field is empty";
        $valErr = true;
    } elseif (!preg_match("/^(?:[01]\d|2[0-3]):[0-5]\d$/", $_POST["time"])) {
        $_SESSION["timeErr"] = "Invalid time";
        $valErr = true;
    } elseif (strtotime($_POST["date"] . " " . $_POST["time"]) > strtotime(date("Y-m-d H:i"))) { 
        // Combines date and time to check if they exceed the current server date and time
        $_SESSION["timeErr"] = "Time cannot be in the future";
        $valErr = true;
    }


    // Keep user input persistent in form
    $_SESSION["systolic"] = $_POST["systolic"];
    $_SESSION["diastolic"] = $_POST["diastolic"];
    $_SESSION["heart_rate"] = $_POST["heart_rate"];
    $_SESSION["time"] = $_POST["time"];
    $_SESSION["date"] = $_POST["date"];

    // If no validation errors, proceed to insert data into the database
    if (!$valErr) {
        $username = $_SESSION["loggedIn_username"];
        $readingDate = $_POST["date"];
        $readingTime = $_POST["time"];
        $systolic = (int)$_POST["systolic"];
        $diastolic = (int)$_POST["diastolic"];
        $heartRate = (int)$_POST["heart_rate"];

        // Call the function to add blood pressure reading
        $result = addBloodPressureReading($username, $readingDate, $readingTime, $systolic, $diastolic, $heartRate);


        // Handle result
        if ($result===true) {
            
            $userType =  $_SESSION["userType"];
            
            //unset all variables 
            session_unset(); 
            $_SESSION["loggedIn_username"] = $username; // Restore username
            $_SESSION["userType"] = $userType; // Restore user Type

            $_SESSION["dbMessage"] = "Record Added Successfully";

            //Calls a function that checks if patient's readings are in ranges and notifies their support network if readings are out of range
            check_if_patient_readings_are_in_Range($systolic,$diastolic,$heartRate);

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


    function check_if_patient_readings_are_in_Range($systolic,$diastolic,$heartRate){
        //Check if readings are out of range and and send alert via phone number and email to support network
        $result=checkPatientReading($_SESSION["loggedIn_username"],$systolic,$diastolic);

        if (isset($result['error'])) {//If an error occur store it in a session that will display it to the user
            $_SESSION["patientRange_Err"]=$result['error'];
            
        } 
        /*If there procedure return a email or phone number it means the patients readings are out of range 
        and that means their support network need to be contacted*/
        else if (findelement($result,"email")&& findelement($result,"phone_number")) {

            // Initialize an array to store all support network email addresses
            $supportNetworkEmail = array();

            // Iterate through `$result` (assuming `$result` is an array of associative arrays)
            foreach ($result as $entry) {
                if (isset($entry['email'])) { // Ensure 'email' exists in the current entry
                    $supportNetworkEmail[] = $entry['email']; // Add email to the array
                }
            }


            // Send email alerting your support netwoek about your irregular reading
            require_once('sendmail.php');
            $emailResult = sendAlertEmailToSupportNetword($supportNetworkEmail,$result[0]['Patient_Name'], $result[0]['Recommended_Min_Systolic'],$result[0]['Recommended_Min_Diastolic'], $result[0]['Recommended_Max_Systolic'], $result[0]['Recommended_Max_Diastolic'], $systolic,$diastolic,$heartRate);

            if($emailResult){
                $_SESSION["patientRange_Err"]= "Your support Network was notified of your irregular reading.";
            }
            else {
                $_SESSION["patientRange_Err"]="An error Occurred while emailing your support network";
            }

        }
        else if (findelement($result,"message")){
            print_r($result);
            $_SESSION["patientRange_Err"]=$result[0]["message"];
        }

    }
    
    //function used to find if there is an array with a certain element or record in it eg email or phone number
    function findelement($data, $search) {
        if (is_array($data)) {
            foreach ($data as $entry) {
                if (isset($entry[$search])) {
                    return true; // Key found
                }
            }
        }
        return false; // Explicitly return false if key is not found
    }