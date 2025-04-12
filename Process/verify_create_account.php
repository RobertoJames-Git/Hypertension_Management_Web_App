<?php

    require_once("sendmail.php");
    require_once("../Database/database_actions.php");
    require_once("sanitizeData.php");
    
    session_start();

    #redirect user if a form was not submitted to this page
    if(!isset($_POST["account_creation"],$_POST["fname"],$_POST["lname"],$_POST["gender"],$_POST["dob"],$_POST["user_type"],$_POST["email"])){
        header("location:../create_account.php");
        exit();
    }
    else{

        #unsets any previous error message
        
        unset(  $_SESSION["fnameErr"],$_SESSION["lnameErr"],$_SESSION["dobErr"],
                $_SESSION["user_typeErr"],$_SESSION["genderErr"],$_SESSION["emailErr"],
                $_SESSION["family_edu_level_Err"],$_SESSION["health_prov_exp_Err"],
                $_SESSION["phoneNum_Err"]);
        
        #this variable will keep track of if there were any validation errors
        $valErr=false;

        //Calls a user defined function to sanitize input 

        $_POST = sanitizeUserInput($_POST); // Overwrite after sanitization
        
        #stores the users input in a session to make their info persistent in the form
        $_SESSION["fname"]=$_POST["fname"];
        $_SESSION["lname"]=$_POST["lname"];
        $_SESSION["gender"]=$_POST["gender"];
        $_SESSION["dob"]=$_POST["dob"];
        $_SESSION["user_type"]=$_POST["user_type"];
        $_SESSION["email"]=$_POST["email"];
        $_SESSION["family_edu_level"]=$_POST["family_edu_level"];
        $_SESSION["health_prov_exp"]=$_POST["health_prov_exp"];
        $_SESSION["phoneNum"]=$_POST["phoneNum"];

        if($_POST["fname"]==""){
            $_SESSION["fnameErr"]="Field is empty";
            $valErr=true;
        }
        else if(strlen($_POST["fname"])==1){
            $_SESSION["fnameErr"]="Invalid First Name";
            $valErr=true;
        }
        //check if the field contains any numbers
        else if(preg_match("/[0-9]/",$_POST["fname"])){
            $_SESSION["fnameErr"]="Name cannot be a number";
            $valErr=true; 
        }
        //check if the field only has alphabetical characters
        else if (preg_match('/[^A-za-z]/', $_POST["fname"])) {
            $_SESSION["fnameErr"] = "Remove any symbols";
            $valErr = true; 
        }
        

        
        if($_POST["lname"]==""){
            $_SESSION["lnameErr"]="Field is empty";
            $valErr=true;
        }
        else if(strlen($_POST["lname"])==1){
            $_SESSION["lnameErr"]="Invalid Last Name";
            $valErr=true;
        }
        else if(preg_match("/[0-9]/",$_POST["lname"])){
            $_SESSION["lnameErr"]="Remove any numbers";
            $valErr=true; 
        }
        else if (preg_match('/[^A-Za-z]/', $_POST["lname"])) {
            $_SESSION["lnameErr"] = "Remove any symbols";
            $valErr = true; 
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


        

        if($_POST["email"]==""){
            $_SESSION["emailErr"]="Field is empty";
            $valErr=true;
        }
        //check if the email recieved follows a valid format
        else if(!filter_var($_POST["email"],FILTER_VALIDATE_EMAIL)){
            $_SESSION["emailErr"]="Invalid Email";
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
            if(!isset($_POST["family_edu_level"])){
                $_SESSION["family_edu_level_Err"] = "Missing in Request";
                $valErr=true; 
                header("location:../create_account.php");
                exit();
            }
            else if($_POST["family_edu_level"]==""){
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
            if(!isset($_POST["health_prov_exp"])){
                $_SESSION["health_prov_exp_Err"] = "Missing in Request";
                $valErr=true; 
                header("location:../create_account.php");
                exit();
            }
            else if($_POST["health_prov_exp"]==""){
                $_SESSION["health_prov_exp_Err"] = "Field is empty";
                $valErr=true; 
            }
            //check if the educaion level recieved is valid
            else if(!in_array($_POST["health_prov_exp"],["Less than a year","One to two years","Three to Fours years","Five years or more","Over a decade"])){
                $_SESSION["health_prov_exp_Err"] = "Invalid Option";
                $valErr=true; 
            }
        }


        //check if the username is set and if it is empty
        if(!isset($_POST["phoneNum"])){
            $_SESSION["phoneNum_Err"] = "Missing in Request";
            $valErr=true; 
            header("location:../create_account.php");
            exit();
        }
        else if ($_POST["phoneNum"]==""){
            $_SESSION["phoneNum_Err"] = "Field is empty";
            $valErr=true; 
            header("location:../create_account.php");
            exit();
        }
        else{

            //check if the number starts with the area code 658 or 876 and it has 10 digits.
            //also the number can have dashed
            if (!preg_match("/^(658|876)-?[0-9]{3}-?[0-9]{4}$/", $_POST["phoneNum"])) {
                $_SESSION["phoneNum_Err"] = "Format: 658-123-4567 or 876-123-4567 or 6581234567 or 8761234567";
                $valErr = true;
            }
        }
        




        if($valErr==true){//redirect user if there is an error in validation

            header("location:../create_account.php"); 
            exit();//Prevents code from running from this point on
        }
        

        #if there were no validation error then all variables that store validation data is unset
        unset(  $_SESSION["fnameErr"],$_SESSION["lnameErr"],$_SESSION["dobErr"],
                $_SESSION["user_typeErr"],$_SESSION["genderErr"],$_SESSION["emailErr"],
                $_SESSION["family_edu_level_Err"],$_SESSION["health_prov_exp_Err"],$_SESSION["database_or_sendmail_Err"],
                $_SESSION["username"],$_SESSION["phoneNum_Err"]);
        
        //calls a function from the databse folder that adds the user to the database
        if(addUserToDatabase()==false){//if an error occurs while adding the user account then they are redirected back tot the form
            header("location:../create_account.php");

        }
        

        if(!isset($_SESSION["username"])||$_SESSION["username"]==""){
            $_SESSION["database_or_sendmail_Err"]="Could not generate username";
            header("location:../create_account.php");

        }

        //send activation email to user
        $emailResponse=sendActivationEmail($_SESSION["email"]);

        if($emailResponse==false){
            $_SESSION["database_or_sendmail_Err"]="Email with activation code coud not be sent";
            header("location:../create_account.php");
        }
        else if($emailResponse==true){
            $_SESSION["email_status"]=true;
        }
        else{
            //store error:  Not all data needed to send an email is set
            $_SESSION["database_or_sendmail_Err"]=$emailResponse;
        }
        
        header("location:../emailAlert.php");
        
    }


    #check if a date is valid
    function validateDate($date, $format = 'Y-m-d') {
        // Attempt to create a DateTime object using the given format
        $d = DateTime::createFromFormat($format, $date);
        
        // Check if the DateTime object was created successfully AND
        // ensure the formatted output matches the input date exactly
        return $d && $d->format($format) === $date;
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
    
