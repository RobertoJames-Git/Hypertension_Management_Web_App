
<?php

    session_start();
    if(!isset($_POST["account_creation"])){
        header("location:../create_account.php");
    }
    else{

        unset($_SESSION["fnameErr"],$_SESSION["lnameErr"],$_SESSION["dobErr"],$_SESSION["user_typeErr"],$_SESSION["genderErr"],$_SESSION["emailErr"]);
        

        $valErr=false;

        //Calls a user defined function to sanitize input
        $_POST["fname"] = sanitizeInput($_POST["fname"]);
        $_POST["lname"]= sanitizeInput($_POST["lname"]);
        $_POST["dob"]= sanitizeInput($_POST["dob"]);
        $_POST["user_type"]= sanitizeInput($_POST["user_type"]);
        $_POST["email"]= sanitizeInput($_POST["email"]);
        $_POST["gender"]= sanitizeInput($_POST["gender"]);

        if($_POST["fname"]==""){
            $_SESSION["fnameErr"]="Field is empty";
            $valErr=true;
        }
        else if(strlen($_POST["fname"])==1){
            $_SESSION["fnameErr"]="Invalid First Name";
            $valErr=true;
        }
        else if(preg_match("/^[0-9]/",$_POST["fname"])){
            $_SESSION["fnameErr"]="Name cannot be a number";
            $valErr=true; 
        }


        
        if($_POST["lname"]==""){
            $_SESSION["lnameErr"]="Field is empty";
            $valErr=true;
        }
        else if(strlen($_POST["lname"])==1){
            $_SESSION["lnameErr"]="Invalid Last Name";
            $valErr=true;
        }
        else if(preg_match("/^[0-9]/",$_POST["lname"])){
            $_SESSION["lnameErr"]="Name cannot be a number";
            $valErr=true; 
        }

        if($_POST["gender"]==""){
            $_SESSION["genderErr"]="Field is empty";
            $valErr=true; 
        }
        else if(!in_array($_POST["gender"],["male","female","other","rather not say"],true)){
            $_SESSION["genderErr"]="Invalid Gender";
            $valErr=true;
        }

        if($_POST["dob"]==""){
            $_SESSION["dobErr"]="Field is empty";
            $valErr=true;
        }
        //calls a user defined function to check if the date recieved is a valid date 
        else if(!validateDate($_POST["dob"])){
            $_SESSION["dobErr"]="Field is empty";
            $valErr=true;
        }


        if($_POST["user_type"]==""){
            $_SESSION["user_typeErr"]="Field is empty";
            $valErr=true;
        }
        //check if the user type recieved is valid
        else if (!in_array($_POST["user_type"], ["Hypertensive Individual", "Family Member", "Healthcare Professional"], true)) {
            $_SESSION["user_typeErr"] = "Invalid user type";
            $valErr=true;
        }


        if($_POST["email"]==""){
            $_SESSION["emailErr"]="Field is empty";
            $valErr=true;
        }
        //check if the email recieved follows a valid format
        else if(!filter_var($_POST["email"],FILTER_VALIDATE_EMAIL)){
            $_SESSION["emailErr"]="Invalid Email";
            $valErr=true;
        }

        if($valErr==true){
            $_SESSION["fname"]=$_POST["fname"];
            $_SESSION["lname"]=$_POST["lname"];
            $_SESSION["gender"]=$_POST["gender"];
            $_SESSION["dob"]=$_POST["dob"];
            $_SESSION["user_type"]=$_POST["user_type"];
            $_SESSION["email"]=$_POST["email"];
            header("location:../create_account.php"); 
        }
        else{
            unset($_SESSION["fnameErr"],$_SESSION["lnameErr"],$_SESSION["dobErr"],$_SESSION["user_typeErr"],$_SESSION["genderErr"]);
            
            /* 
            
            Send verification code to email
            */
        }
    }


    #check if a date is valid
    function validateDate($date, $format = 'Y-m-d') {
        // Attempt to create a DateTime object using the given format
        $d = DateTime::createFromFormat($format, $date);
        
        // Check if the DateTime object was created successfully AND
        // ensure the formatted output matches the input date exactly
        return $d && $d->format($format) === $date;
    }

    function sanitizeInput($dataToSanitize) {
        // Remove any leading or trailing whitespace
        $dataToSanitize = trim($dataToSanitize);
    
        // Convert special characters into HTML entities to prevent XSS attacks
        $dataToSanitize = filter_var($dataToSanitize, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
        // Return the sanitized input
        return $dataToSanitize;
    }
    



