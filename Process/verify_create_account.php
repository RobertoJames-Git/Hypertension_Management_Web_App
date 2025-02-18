<?php

    require_once("sendmail.php");
    require_once("../Database/database_actions.php");

    session_start();

    #redirect user if a form was not submitted to this page
    if(!isset($_POST["account_creation"])){
        header("location:../create_account.php");
    }
    else{

        #unsets any previous error message
        
        unset(  $_SESSION["fnameErr"],$_SESSION["lnameErr"],$_SESSION["dobErr"],
                $_SESSION["user_typeErr"],$_SESSION["genderErr"],$_SESSION["emailErr"],
                $_SESSION["family_edu_level_Err"],$_SESSION["health_prov_exp_Err"]);
        
        #this variable will keep track of if there were any validation errors
        $valErr=false;

        //Calls a user defined function to sanitize input 
        $sanitized_post = [];
        foreach ($_POST as $key => $value) {
            $sanitized_post[$key] = sanitizeInput($value);
        }
        $_POST = $sanitized_post; // Overwrite after sanitization
        


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
        else if(calculateAge($_POST["dob"])<18){
            $_SESSION["dobErr"]="Must be 18 or older";
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
        
        if($_POST["user_type"]=="Family Member"){
            if($_POST["family_edu_level"]==""){
                $_SESSION["family_edu_level_Err"] = "Field is empty";
                $valErr=true; 
            }
            //check if the education level recieved is valid
            else if(!in_array($_POST["family_edu_level"],["No Formal Education","Elementary","Secondary","Some Tertiary","Vocational Training","Degree"])){
                $_SESSION["family_edu_level_Err"] = "Invalid Option";
                $valErr=true; 
            }
        }

        else if($_POST["user_type"]=="Healthcare Professional"){
            
            if($_POST["health_prov_exp"]==""){
                $_SESSION["health_prov_exp_Err"] = "Field is empty";
                $valErr=true; 
            }
            //check if the educaion level recieved is valid
            else if(!in_array($_POST["health_prov_exp"],["Less than a year","One to two years","Three to Fours years","Five years or more","Over a decade"])){
                $_SESSION["health_prov_exp_Err"] = "Invalid Option";
                $valErr=true; 
            }
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


        
        #stores the users input in a session to make their info persistent in the form
        if($valErr==true){
            $_SESSION["fname"]=$_POST["fname"];
            $_SESSION["lname"]=$_POST["lname"];
            $_SESSION["gender"]=$_POST["gender"];
            $_SESSION["dob"]=$_POST["dob"];
            $_SESSION["user_type"]=$_POST["user_type"];
            $_SESSION["email"]=$_POST["email"];
            $_SESSION["family_edu_level"]=$_POST["family_edu_level"];
            $_SESSION["health_prov_exp"]=$_POST["health_prov_exp"];
            header("location:../create_account.php"); 
        }
        else{
            #if there were no validation error then all variables that store validation data is unset
            unset(  $_SESSION["fnameErr"],$_SESSION["lnameErr"],$_SESSION["dobErr"],
                    $_SESSION["user_typeErr"],$_SESSION["genderErr"],$_SESSION["emailErr"],
                    $_SESSION["family_edu_level_Err"],$_SESSION["health_prov_exp_Err"]);
            
            //calls a function from the databse folder that adds the user to the database
            if(!addUserToDatabase()){//if an error occurs while adding the user account then they are redirected back tot the form
                header("location:../create_account.php");
                die();
            }
            
            /* 
                Send activation link to email
            */
            $body="<p>
                Dear ".$_SESSION['fname'].' '.$_SESSION['lname'].",
                <br>
                Thank you for registering with HypMonitor! We're excited to have you on board as a part of our health-focused community. Whether you are a hypertensive individual, a patient, or a healthcare professional, we are here to support you on your journey.
                <br>
                To activate your account and get started, please click the link below:

                <br><br>
                Username:
                <br>Password: ".$_SESSION['password']."<br>
                <a href='http://localhost/Major_Project_DHI/create_account.php'>Activate your account</a>
                <br><br>
                This link will verify your email and confirm that you are the one creating the account. If you don't click on the activation link by the end of the day, your account will be automatically removed.

                If you didn't create this account, please disregard this email.
                <br>
                We're here if you need any assistance.
                <br><br>
                Best regards,

                <p>";

            sendmail($_SESSION["email"],"Welcome To HypMonitor! Activate your Account",$body);
            
            header("location:../emailAlert.php");
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
        $sanitizedData = filter_var($dataToSanitize, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    
        // Return the sanitized input
        return $sanitizedData;
    }


    function calculateAge($birthdate) {
        // Convert the birthdate string into a DateTime object
        $dob = new DateTime($birthdate);
        
        // Get the current date
        $today = new DateTime();
    
        // Calculate the age difference
        $age = $today->diff($dob)->y;
    
        return $age;
    }
    
