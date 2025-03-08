<?php

    require('database_connection.php');



    function addUserToDatabase() {
        try {
            $dbConn = getDatabaseConnection();
            $sql = "CALL AddWebUser(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @generated_username)";
            $stmt = $dbConn->prepare($sql);
    
            $_SESSION["token"] = bin2hex(random_bytes(32));
            $_SESSION["password"] = generateRandomPassword();
            $password_hash = password_hash($_SESSION["password"], PASSWORD_DEFAULT);
    
            $account_status = "pending";
    
            // Bind parameters
            $stmt->bind_param("ssssssssssi",  
                $_SESSION["fname"], 
                $_SESSION["lname"], 
                $_SESSION["gender"], 
                $_SESSION["dob"], 
                $_SESSION["email"], 
                $_SESSION["token"],
                $account_status, 
                $password_hash, 
                $_SESSION["user_type"], 
                $_SESSION["family_edu_level"],
                $_SESSION["health_prov_exp"]
            );
    
            $stmt->execute();
    
            // Retrieve the OUT parameter value
            $result = $dbConn->query("SELECT @generated_username AS username");
    
            // Fetch the username
            if ($row = $result->fetch_assoc()) {
                $_SESSION["username"] = $row["username"];
            } else {
                $_SESSION["database_or_sendmail_Err"] = "Failed to retrieve username.";
                return false; // Ensure function returns false if username retrieval fails
            }
    
            return true; // Success
        } 
        catch (mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), 'Email already exists') !== false) {
                $_SESSION["emailErr"] = "Email already registered.";
            } else {
                $_SESSION["database_or_sendmail_Err"] = "Database Error: " . $e->getMessage();
            }
            return false; // Error occurred
        } 
        finally {
            // Close resources safely
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($dbConn) && $dbConn instanceof mysqli) {
                $dbConn->close();
            }
        }
    }
    

    function activateAccount($token) {
        $dbConn = getDatabaseConnection(); 
    
        try {
            // Prepare the stored procedure call
            $stmt = $dbConn->prepare("CALL ActivateUser(?)");

            // Bind the token parameter
            $stmt->bind_param("s", $token);
    
            // Execute the statement
            $stmt->execute();
    
            // If execution reaches here, the account was successfully activated
            $message = "Account successfully activated!";

            return $message;
        } catch (mysqli_sql_exception $e) {
            // Catch MySQL errors, including SIGNAL errors from the procedure
            $message = $e->getMessage();

            if(strpos($message,"Activation link expired")){
                modifyTokenAndPassword($token);
            }
            return $message;
        } finally {
            // Close resources
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($dbConn) && $dbConn instanceof mysqli) {
                $dbConn->close();
            }
        }
    
        return $message;
    }
    
    function modifyTokenAndPassword($currentToken) {
        
        $dbConn = getDatabaseConnection();
        
        // Generate new token
        $newToken = bin2hex(random_bytes(32));
        
        // Generate new password
        $newPassword = generateRandomPassword();
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
        try {
            // Send data to be updated in procedure
            $stmt = $dbConn->prepare("CALL UpdateUserTokenAndPassword(?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Statement preparation failed: " . $dbConn->error);
            }
    
            $stmt->bind_param("sss", $currentToken, $newToken, $hashedPassword);
            $stmt->execute();
    
            $result = $stmt->get_result(); // Fetch the result set
            $row = $result->fetch_assoc();
    
            if ($row) { // Ensure row is not null
                // Setting variables that will be used to send email later
                $_SESSION['fname'] = $row['fname'];
                $_SESSION['lname'] = $row['lname'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['password'] = $newPassword; // Store plaintext password for email
                $_SESSION["token"] = $newToken;
    
                return true; // Success
            } else {
                $_SESSION["database_or_sendmail_Err"] ="No Records corresponded to the token"; // No matching record
                return false;
            }
        } catch (mysqli_sql_exception $e) {
            $_SESSION["database_or_sendmail_Err"] ="Database Error: " . $e->getMessage();
            return false; 
        } catch (Exception $e) {
            $_SESSION["database_or_sendmail_Err"] ="Error: " . $e->getMessage();
            return false;
        } finally {
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($dbConn) && $dbConn instanceof mysqli) {
                $dbConn->close();
            }
        }
    }
    

    function validateUser($username,$password) {
        $dbConn = getDatabaseConnection();
        $password_hash="";
        try {
            // Prepare the stored procedure call
            $stmt = $dbConn->prepare("CALL GetUserPassword(?)");
    
            // Bind the username parameter
            $stmt->bind_param("s", $username);
    
            // Execute the statement
            $stmt->execute();
    
            // Fetch the result
            $result = $stmt->get_result();
            

            // If there is a result, fetch the password
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $password_hash = $row['password'];
            } else {
                return "Unknown error occurred.";
            }
            
            //if password match was successful
            if(password_verify($password,$password_hash)){
                return "Success";
            }
            else{
                return "Invalid username and/or password";
            }
            
            
        } catch (mysqli_sql_exception $e) {
            // Catch MySQL errors, including SIGNAL errors from the procedure

            return $e->getMessage();
    
            
        } finally {
            // Close resources
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($dbConn) && $dbConn instanceof mysqli) {
                $dbConn->close();
            }
        }
    }
    
    
    function addBloodPressureReading($username, $readingDate, $readingTime, $systolic, $diastolic, $heartRate) {
        $dbConn = getDatabaseConnection(); 
    
        try {
            // Prepare the stored procedure call
            $stmt = $dbConn->prepare("CALL AddBloodPressureReading(?, ?, ?, ?, ?, ?)");
    
            // Bind parameters
            $stmt->bind_param("sssiii", $username, $readingDate, $readingTime, $systolic, $diastolic, $heartRate);
    
            // Execute the statement
            $stmt->execute();
    
            // If execution reaches here, the reading was successfully added
            $message = true;
    
            return $message;
        } catch (mysqli_sql_exception $e) {
            // Catch MySQL errors, including SIGNAL errors from the procedure
            $message = $e->getMessage();
            return $message;
        } finally {
            // Close resources
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($dbConn) && $dbConn instanceof mysqli) {
                $dbConn->close();
            }
        }
    
        return $message;
    }
    

    function getBloodPressureReadings($username, $page = 1) {
        $dbConn = getDatabaseConnection();
    
        try {
            $stmt = $dbConn->prepare("CALL GetBloodPressureReadings(?, ?)");
            $stmt->bind_param("si", $username, $page);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $readings = [];
    
            while ($row = $result->fetch_assoc()) {
                $readings[] = $row;
            }
    
            return $readings;
        } catch (mysqli_sql_exception $e) {
            return ["error" => $e->getMessage()]; // Capture error messages from the procedure
        } finally {
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($dbConn) && $dbConn instanceof mysqli) {
                $dbConn->close();
            }
        }
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

