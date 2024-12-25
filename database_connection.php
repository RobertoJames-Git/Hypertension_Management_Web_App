<?php
    /*
        website name: https://www.freemysqlhosting.net/account/
        https://www.phpmyadmin.co/db_structure.php?server=1&db=sql3752921
        
        Host: sql3.freesqldatabase.com
        Database name: sql3752921
        Database user: sql3752921
        Database password: bqRqVzWRZ6
        Port number: 3306
    */
    $hostname="sql3.freesqldatabase.com";
    $database_name=$database_user="sql3752921";
    $password="bqRqVzWRZ6";

    try{
        $dbConn=mysqli_connect($hostname,$database_user,$password,$database_name);
    }
    catch(mysqli_sql_exception $e){
        echo("Connection Failed");
    }


?>