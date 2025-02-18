<?php

    require('database_connection.php');



    function addUserToDatabase(){
        try{
            $dbConn=getDatabaseConnection();
            $sql = "CALL AddWebUser(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $dbConn->prepare($sql);
            
            $token= bin2hex(random_bytes(32));
            $_SESSION["password"]=generateRandomPassword();
            $password_hash= password_hash($_SESSION["password"],PASSWORD_DEFAULT);
            
            $time = new DateTime(); // Get current time
            $time->modify('+30 minutes'); // Add 30 minutes
            $token_expiration= $time->format('Y-m-d H:i:s'); // Display the new time
            $account_status="pending";
            // Bind parameters
            $stmt->bind_param("sssssssssssi",  
                $_SESSION["fname"], 
                $_SESSION["lname"], 
                $_SESSION["gender"], 
                $_SESSION["dob"], 
                $_SESSION["email"], 
                $token, 
                $token_expiration,
                $account_status, 
                $password_hash, 
                $_SESSION["user_type"], 
                $_SESSION["family_edu_level"], 
                $_SESSION["health_prov_exp"]
            );

            $stmt->execute();
        }
        catch(mysqli_sql_exception $e){

            $_SESSION["emailErr"]="Email already registered";
            return false;//to indicate a error occurred
        }

        return true;
    }


    function generateRandomPassword() {
        
        do{
            $length = rand(8, 12); // Random length between 8 and 12
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
            $password = '';

            for ($i = 0; $i < $length; $i++) {
                $password .= $characters[rand(0, strlen($characters) - 1)];
            }

        }while( !preg_match('/[A-Z]/', $password) ||  // Must contain an uppercase letter
                !preg_match('/[a-z]/', $password) ||  // Must contain a lowercase letter
                !preg_match('/[0-9]/', $password) ||  // Must contain a number
                !preg_match('/[!@#$%^&*()\-_+=]/', $password)); // Must contain a symbol;

        return $password;
    }


