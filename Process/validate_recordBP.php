<?php

    session_start();
    require_once("sanitizeData.php");

    if(!isset($_POST["bp_record"],$_POST["systolic"],$_POST["diastolic"],$_POST["heart_rate"],$_POST["time"],$_POST["date"])){
        header("location:../recordBP.php");
        exit();//remaining code not executed
    }

    $valErr=false;

    //sanitize input
    $_POST = sanitizeUserInput($_POST);
    
    if($_POST["systolic"]==""){
        $_SESSION["systolicErr"]="Field is empty";
        $valErr=true;
    }
    else if(!is_int($_POST["systolic"])){
        $_SESSION["systolicErr"]="Numbers Only";
        $valErr=true;
    }

    //keep user input persistent in form
    $_SESSION["systolic"]=$_POST["systolic"];
    $_SESSION["diastolic"]=$_POST["diastolic"];
    $_SESSION["heart_rate"]=$_POST["heart_rate"];
    $_SESSION["time"]=$_POST["time"];
    $_SESSION["date"]=$_POST["date"];