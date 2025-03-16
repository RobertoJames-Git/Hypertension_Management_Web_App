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
            $stmt->bind_param("sssssssssss",  
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
    

    function validateUser($username, $password) {
        $dbConn = getDatabaseConnection();
        $password_hash = "";
        $user_type = "";
    
        try {
            // Prepare the stored procedure call
            $stmt = $dbConn->prepare("CALL GetUserCredentialsAndType(?)");
    
            // Bind the username parameter
            $stmt->bind_param("s", $username);
    
            // Execute the statement
            $stmt->execute();
    
            // Fetch the result
            $result = $stmt->get_result();
    
            // If there is a result, fetch the password and user type
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $password_hash = $row['password'];
                $user_type = $row['user_type'];
            } else {
                return "Unknown error occurred.";
            }
    
            // If password matches
            if (password_verify($password, $password_hash)) {
                return [
                    "status" => "Success",
                    "user_type" => $user_type
                ];
            } else {
                return "Invalid username and/or password.";
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
    

    function getBloodPressureReadings($username, $page = 1,$numOfRecordsToDisplay) {
        $dbConn = getDatabaseConnection();
    
        try {

            // Ensure the variable is an integer (sanitize user input)
            $numOfRecordsToDisplay = (int) $numOfRecordsToDisplay;

            // If the value is invalid (e.g., 0 or non-numeric), set it to the default value (10)
            if ($numOfRecordsToDisplay <= 0) {
                $numOfRecordsToDisplay = 10;
            }

            // If the number of records exceeds the maximum limit (60), set it to the max
            if ($numOfRecordsToDisplay > 60) {
                $numOfRecordsToDisplay = 60;
            }


            $stmt = $dbConn->prepare("CALL GetBloodPressureReadings(?, ?, ?)");
            $stmt->bind_param("sii", $username, $page,$numOfRecordsToDisplay);
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
    
    
    function getMatchingUsers($usernamePrefix, $accountType, $loggedInUsername) {
        // Get the database connection
        $dbConn = getDatabaseConnection();
    
        try {
            // Validate account type to avoid unnecessary calls to the procedure
            if ($accountType !== 'Family member' && $accountType !== 'Health Care Professional' && $accountType !== 'Patient' ) {
                throw new InvalidArgumentException('Invalid account type. Must be "Family member" or "Health Care Professional" or "Patient".');
            }
    
            // Prepare the stored procedure call
            $stmt = $dbConn->prepare("CALL GetMatchingUsers(?, ?, ?)");
    
            // Bind parameters to the procedure
            $stmt->bind_param("sss", $usernamePrefix, $accountType, $loggedInUsername);
    
            // Execute the procedure
            $stmt->execute();
    
            // Fetch results
            $result = $stmt->get_result();
            $matchingUsers = [];
            if ($result) {

                //print_r($result->fetch_assoc());
                // Process each row returned from the procedure
                while ($row = $result->fetch_assoc()) {
                    // Add both the username and request_status to the result array
                    $matchingUsers[] = [
                        'username' =>  $row['family_member_username'] ?? $row['healthcare_prof_username'] ?? $row['patient_username'],
                        'request_status' => $row['request_status']
                    ];
                }
            }
    
            // Return the array of matching users with their request status
            return $matchingUsers;
        } catch (mysqli_sql_exception $e) {
            // Catch MySQL errors, including SIGNAL errors from the procedure
            return ['error' => $e->getMessage()];
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
    
    

    function populateSupportTable($patientUsername, $familyUsername) {
        // Get the database connection
        $dbConn = getDatabaseConnection();
    
        try {
            
            //use local timezone
            date_default_timezone_set("America/Jamaica");

            $startDate_and_Time= date("Y-m-d H:i:s");
            // Prepare the stored procedure call

            $stmt = $dbConn->prepare("CALL PopulateSupportTable(?, ?, ?)");
    
            // Bind parameters to the procedure
            $stmt->bind_param("sss", $patientUsername, $familyUsername, $startDate_and_Time);
    
            // Execute the procedure
            $stmt->execute();
    
            // Success message if the execution reaches here
            $message = "Support network relationship successfully added with status 'pending'.";
            return $message;
    
        } catch (mysqli_sql_exception $e) {
            // Catch MySQL errors, including any SIGNAL errors from the procedure
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
    }
    


    function getPendingRequestsForPatient($patientUsername) {
        // Get the database connection
        $dbConn = getDatabaseConnection();
    
        try {
            // Prepare the stored procedure call
            $stmt = $dbConn->prepare("CALL GetPatientPendingRequests(?)");
    
            // Bind the input parameter (patient username)
            $stmt->bind_param("s", $patientUsername);
    
            // Execute the procedure
            $stmt->execute();
    
            // Fetch the results
            $result = $stmt->get_result();
            $pendingRequests = [];
    
            // Process each row in the result set
            while ($row = $result->fetch_assoc()) {
                // Format the request date as "Mar 10, 2025"
                $formattedDate = date("M j, Y", strtotime($row['request_date']));
    
                $pendingRequests[] = [
                    'sender_role' => $row['sender_role'],          // Role of the sender
                    'sender_username' => $row['sender_username'],  // Username of the sender
                    'request_date' => $formattedDate,              // Formatted date
                    'request_status' => $row['request_status'],    // Status of the request (e.g., pending)
                ];
            }
    
            // Return the array of pending requests
            return $pendingRequests;
        } catch (mysqli_sql_exception $e) {
            // Handle MySQL errors
            return ['error' => $e->getMessage()];
        } finally {
            // Close the resources
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($dbConn) && $dbConn instanceof mysqli) {
                $dbConn->close();
            }
        }
    }
    

    function sendMonitorRequest($senderUsername, $recipientUsername) {
        // Get the database connection
        $dbConn = getDatabaseConnection();
    
        try {
            // Set the timezone to Jamaica
            $jamaicanTime = new DateTime("now", new DateTimeZone("America/Jamaica"));
            $startDate = $jamaicanTime->format("Y-m-d H:i:s"); // Format to 'YYYY-MM-DD HH:MM:SS'
    
            // Prepare the stored procedure call
            $stmt = $dbConn->prepare("CALL ManageMonitorRequest(?, ?, ?)");
    
            // Bind parameters to the procedure
            $stmt->bind_param("sss", $senderUsername, $recipientUsername, $startDate);
    
            // Execute the procedure
            $stmt->execute();
    
            // Return success if the procedure executes without errors
            return  "Monitor request processed.";
        } catch (mysqli_sql_exception $e) {
            // Catch MySQL exceptions and return the error message
            return $e->getMessage();
        } finally {
            // Close the statement and database connection
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

