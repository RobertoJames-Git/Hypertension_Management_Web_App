
<?php

    session_start();
    if(!isset($_POST["account_creation"])){
        header("location:../create_account.php");
    }
    else{

        unset($_SESSION["fnameErr"],$_SESSION["lnameErr"],$_SESSION["dobErr"],$_SESSION["user_typeErr"]);
        
        $valErr=false;
        $_POST["fname"] =trim($_POST["fname"]);
        $_POST["lname"]= trim($_POST["lname"]);

        if(trim($_POST["fname"])==""){
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

        if(trim($_POST["dob"])==""){
            $_SESSION["dobErr"]="Field is empty";
            $valErr=true;
        }
        else if()
        echo $_POST["dob"];

        if(trim($_POST["user_type"])==""){
            $_SESSION["user_typeErr"]="Field is empty";
            $valErr=true;
        }

        if(trim($_POST["email"])==""){
            $_SESSION["emailErr"]="Field is empty";
            $valErr=true;
        }
        else if(!filter_var($_POST["email"],FILTER_VALIDATE_EMAIL)){
            $_SESSION["emailErr"]="Invalid Email";
            $valErr=true;
        }

        if($valErr==true){
            #header("location:../create_account.php"); 
        }

    }



