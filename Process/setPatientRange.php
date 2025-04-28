<?php


    session_start();

        
    //ensure the user is logged in
    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:../logout.php");
        exit();
    }

    // Check if all required POST variables are set
    if(!isset($_POST["max_systolic"],$_POST["max_diastolic"],$_POST["min_systolic"],$_POST["min_diastolic"])){
        $_SESSION["Err_message"]="Not all form data was sent";
        header("Location:../recordBP.php");
        exit();
    }
    
    require_once("sanitizeData.php");

    // Flag to track validation errors
    $valErr = false;

    // Sanitize user input
    $_POST = sanitizeUserInput($_POST);

    // Initialize error session variables
    $_SESSION["max_systolic"]=$_SESSION["max_diastolic"]=$_SESSION["min_systolic"]=$_SESSION["min_diastolic"]= "";
    $_SESSION["Err_message"]="<ul>";

    // Validate Max Systolic Blood Pressure
    if($_POST["max_systolic"]==""){
        $_SESSION["Err_message"] .="<li>Max systolic Field is Empty</li>";
        $valErr=true;
    } elseif(!filter_var($_POST["max_systolic"], FILTER_VALIDATE_INT)){
        $_SESSION["Err_message"] .="<li> Max Systolic must have only Numbers</li>";
        $valErr=true;
    }
    elseif(intval($_POST["max_systolic"])<50 ||intval($_POST["max_systolic"])>400){
        $_SESSION["Err_message"] .="<li>Max Systolic accepted Range 50 - 400</li>";
        $valErr=true;
    }

    if($_POST["min_systolic"]==""){
        $_SESSION["Err_message"] .="<li>Min systolic Field is Empty</li>";
        $valErr=true;
    } 
    elseif(!filter_var($_POST["min_diastolic"], FILTER_VALIDATE_INT)){
        $_SESSION["Err_message"] .="<li>Min systolic must have only numbers</li>";
        $valErr=true;
    }
    elseif(intval($_POST["min_systolic"])<50 ||intval($_POST["min_systolic"])>400){
        $_SESSION["Err_message"] .="<li>Min systolic Accepted Range 50 - 400</li>";
        $valErr=true;
    }



    // Validate Max Diastolic Blood Pressure
    if($_POST["max_diastolic"]==""){
        $_SESSION["Err_message"] .="<li>Max diastolic Field is Empty</li>";
        $valErr=true;
    } 
    elseif(!filter_var($_POST["max_diastolic"], FILTER_VALIDATE_INT)){
        $_SESSION["Err_message"] .="<li>Max diastolic must have only Numbers</li>";
        $valErr=true;
    }
    elseif(intval($_POST["max_diastolic"])<30 ||intval($_POST["max_diastolic"])>200){
        $_SESSION["Err_message"] .="<li>Max diastolic accepted Range 30 - 200</li>";
        $valErr=true;
    }

    // Validate Max Diastolic Blood Pressure
    if($_POST["min_diastolic"]==""){
        $_SESSION["Err_message"] .="<li>Min diastolic Field is Empty</li>";
        $valErr=true;
    } 
    elseif(!filter_var($_POST["min_diastolic"], FILTER_VALIDATE_INT)){
        $_SESSION["Err_message"] .="<li>Min diastolic must have only Numbers</li>";
        $valErr=true;
    }
    elseif(intval($_POST["min_diastolic"])<30 ||intval($_POST["min_diastolic"])>200){
        $_SESSION["Err_message"] .="<li>Min diastolic Accepted Range 30 - 200</li>";
        $valErr=true;
    }





    //Check if min diastolic is higher than max diastolic
    if($_POST["min_diastolic"]>$_POST["max_diastolic"]){
        $_SESSION["Err_message"] .="<li>Min diastolic cannot be higher than max diastolic</li>";
        $valErr=true;
    }
    //Check if min systolic is higher than max systolic
    if($_POST["min_systolic"]>$_POST["max_systolic"]){
        $_SESSION["Err_message"] .="<li>Min systolic cannot be higher than max systolic</li>";
        $valErr=true;
    }


    $_SESSION["Err_message"].="</ul>";

    
    // Keep user input persistent in form
    $_SESSION["max_systolic"] = $_POST["max_systolic"];
    $_SESSION["max_diastolic"] = $_POST["max_diastolic"];
    $_SESSION["min_systolic"] = $_POST["min_systolic"];
    $_SESSION["min_diastolic"] = $_POST["min_diastolic"];




    if($valErr == false){

        require_once("../Database/database_actions.php"); // Include the file where addBloodPressureReading() is defined
        $result=setPatientRange($_SESSION["loggedIn_username"],$_SESSION["selected_patient"],$_POST["min_systolic"],$_POST["max_systolic"],$_POST["min_diastolic"],$_POST["max_diastolic"]);

       if(isset($result['success'])){

        //unset SESSION vairable after data is successfully added
        unset($_SESSION["max_systolic"],$_SESSION["max_diastolic"],$_SESSION["min_systolic"],$_SESSION["min_diastolic"]);
        
        $_SESSION["Err_message"]=$result['success'];

       }else if ($result['error']){

        $_SESSION["Err_message"]=$result['error'];

       }

   
    }



    header("Location:../recordBP.php");
    
    


    
    

