<?php

    require('database_connection.php');



    function addUserToDatabase() {
        try {
            $dbConn = getDatabaseConnection();
            $sql = "CALL AddWebUser(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @generated_username)";
            $stmt = $dbConn->prepare($sql);
            
            

            do{
                //token is a session because it is needed when sending a email
                $_SESSION["token"] = bin2hex(random_bytes(32));
            }while(tokenExistInDatabase($_SESSION["token"]) == true);

            $_SESSION["password"] = generateRandomPassword();
            $password_hash = password_hash($_SESSION["password"], PASSWORD_DEFAULT);
            

            //remove dashes from phone number
            $_SESSION["phoneNum"] =  str_replace("-", "", $_SESSION["phoneNum"]);
            $account_status = "pending";
    
            // Bind parameters
            $stmt->bind_param("ssssssssssss",  
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
                $_SESSION["health_prov_exp"],
                $_SESSION["phoneNum"]
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

    function tokenExistInDatabase($token) {
        // Get the database connection
        $dbConn = getDatabaseConnection();
        
        // Prepare the SQL query
        $query = "SELECT COUNT(*) FROM web_users WHERE token = ?";
        
        // Initialize a prepared statement
        $stmt = $dbConn->prepare($query);
        
        // Bind the token parameter to the statement
        $stmt->bind_param("s", $token);
        
        // Execute the query
        $stmt->execute();
        
        // Get the result
        $stmt->bind_result($count);
        $stmt->fetch();
        
        // Close the statement and connection
        $stmt->close();
        
        // Return true if the token exists, false otherwise

        if($count > 0){
            return true;
        }
        return false;
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
    
     

    function sendRequest($senderUsername, $recipientUsername) {
        // Get the database connection
        $dbConn = getDatabaseConnection();
    
        try {
            // Set the timezone to Jamaica
            $jamaicanTime = new DateTime("now", new DateTimeZone("America/Jamaica"));
            $startDate = $jamaicanTime->format("Y-m-d H:i:s"); // Format to 'YYYY-MM-DD HH:MM:SS'
    
            // Prepare the stored procedure call
            $stmt = $dbConn->prepare("CALL sendRequest(?, ?, ?)");
    
            // Bind parameters to the procedure
            $stmt->bind_param("sss", $senderUsername, $recipientUsername, $startDate);
    
            // Execute the procedure
            $stmt->execute();
    
            // Return success if the procedure executes without errors
            return  "Request Successfully processed.";
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
    
    
    function getPendingRequests($username) {
        $dbConn = getDatabaseConnection(); 
        
        try {
            // Prepare the stored procedure call
            $stmt = $dbConn->prepare("CALL GetPendingRequests(?)");
        
            // Bind the input parameter
            $stmt->bind_param("s", $username);
        
            // Execute the statement
            $stmt->execute();
        
            // Fetch the results
            $result = $stmt->get_result();
            $pendingRequests = [];
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $pendingRequests[] = [
                        'from_username' => $row['from_username'],
                        'request_status' => $row['request_status'],
                        'request_status' => $row['request_status'],
                        'request_date' => $row['formatted_request_date']
                    ];
                }
            }
        
            // Return the list of pending requests
            return $pendingRequests;
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
    


    function managePendingRequest($senderUsername, $loggedInUsername, $decision) {
        // Get the database connection
        $dbConn = getDatabaseConnection();
    
        try {
            // Validate the decision to avoid invalid calls to the procedure
            if ($decision !== 'accepted' && $decision !== 'rejected') {
                throw new InvalidArgumentException('Invalid decision. Must be "accepted" or "rejected".');
            }
    
            // Prepare the stored procedure call
            $stmt = $dbConn->prepare("CALL managePendingRequest(?, ?, ?)");
    
            // Bind parameters to the procedure
            $stmt->bind_param("sss", $senderUsername, $loggedInUsername, $decision);
    
            // Execute the procedure
            $stmt->execute();
    
            // Fetch the result to check if there were any errors or feedback
            $result = $stmt->get_result();
    
            if ($result) {
                // Handle the result as needed (optional)
                $message = "The request has been successfully updated to '$decision'.";
            } else {
                $message = "No additional information returned.";
            }
    
            // Return success message
            return ['success' => true, 'message' => $message];
        } catch (mysqli_sql_exception $e) {
            // Catch MySQL errors, including SIGNAL errors from the procedure
            return ['success'=> false, 'error' => $e->getMessage()];
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
    

    function getRejectedRequests($loggedInUsername) {
        // Get the database connection
        $dbConn = getDatabaseConnection();
    
        try {
            // Prepare the stored procedure call
            $stmt = $dbConn->prepare("CALL getRejectedRequests(?)");
    
            // Bind parameters
            $stmt->bind_param("s", $loggedInUsername);
    
            // Execute the procedure
            $stmt->execute();
    
            // Fetch results
            $result = $stmt->get_result();
            $rejectedRequests = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $rejectedRequests[] = $row;
                }
            }
    
            // Return the rejected requests
            return $rejectedRequests;
        } catch (mysqli_sql_exception $e) {
            // Handle SQL errors
            return ['error' => $e->getMessage()];
        } finally {
            // Cleanup resources
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($dbConn) && $dbConn instanceof mysqli) {
                $dbConn->close();
            }
        }
    }
    

    function deleteRejectedRequest($requestId) {
        // Get the database connection
        $dbConn = getDatabaseConnection();
    
        try {
            // Prepare the stored procedure call
            $stmt = $dbConn->prepare("CALL deleteRejectedRequest(?)");
    
            // Bind parameters
            $stmt->bind_param("i", $requestId);
    
            // Execute the procedure
            $stmt->execute();
    
            // Return success message
            return ['success' => true];
        } catch (mysqli_sql_exception $e) {
            // Handle SQL errors
            return ['error' => $e->getMessage()];
        } finally {
            // Cleanup resources
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($dbConn) && $dbConn instanceof mysqli) {
                $dbConn->close();
            }
        }
    }
    
    function getSupportNetwork($loggedInUsername) {
        // Get the database connection
        $dbConn = getDatabaseConnection();
    
        try {
            // Call the stored procedure
            $stmt = $dbConn->prepare("CALL getSupportNetwork(?)");
            $stmt->bind_param("s", $loggedInUsername);
            $stmt->execute();
    
            // Fetch results
            $result = $stmt->get_result();
            $supportNetwork = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $supportNetwork[] = $row;
                }
            }
    
            return $supportNetwork;
        } catch (mysqli_sql_exception $e) {
            return ['error' => $e->getMessage()];
        } finally {
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($dbConn) && $dbConn instanceof mysqli) {
                $dbConn->close();
            }
        }
    }
    

    function removeSupportNetworkConnection($loggedInUsername, $supportUsername) {
        // Get the database connection
        $dbConn = getDatabaseConnection();
    
        try {
            // Prepare the stored procedure call
            $stmt = $dbConn->prepare("CALL removeSupportNetwork(?, ?)");
            $stmt->bind_param("ss", $loggedInUsername, $supportUsername);
            $stmt->execute();
    
            return ['success' => true];
        } catch (mysqli_sql_exception $e) {
            return ['error' => $e->getMessage()];
        } finally {
            // Cleanup resources
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($dbConn) && $dbConn instanceof mysqli) {
                $dbConn->close();
            }
        }
    }
    

    function getAcceptedPatients($loggedInUsername) {
        // Get the database connection
        $dbConn = getDatabaseConnection();
    
        try {
            // Prepare the stored procedure call
            $stmt = $dbConn->prepare("CALL GetAcceptedPatients(?)");
    
            // Bind the logged-in username as a parameter
            $stmt->bind_param("s", $loggedInUsername);
    
            // Execute the procedure
            $stmt->execute();
    
            // Fetch the result set
            $result = $stmt->get_result();
            $patients = [];
    
            // Process the result set
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $patients[] = $row; // Add each row to the patients array
                }
            }
    
            return $patients; // Return the list of patients
        } catch (mysqli_sql_exception $e) {
            // Handle SQL exceptions and return the error
            return ['error' => $e->getMessage()];
        } finally {
            // Clean up resources
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($dbConn) && $dbConn instanceof mysqli) {
                $dbConn->close();
            }
        }
    }



    function getAcceptedHealthProfessional($loggedInUsername) {
        // Get the database connection
        $connection = getDatabaseConnection();
        if (!$connection) {
            return ["error" => "Failed to connect to the database"];
        }

        // Call the stored procedure
        $query = "CALL GetAcceptedHealthCareProfessionalsByUsername(?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("s", $loggedInUsername);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $healthCareProfessionals = [];

            // Fetch all results
            while ($row = $result->fetch_assoc()) {
                $healthCareProfessionals[] = $row;
            }

            $stmt->close();
            $connection->close();

            // Return the list of professionals or an error message
            return $healthCareProfessionals;
        } else {
            $stmt->close();
            $connection->close();
            return ["error" => "Failed to execute the stored procedure"];
        }
    }


    function getChatMessages($senderId, $recipientId) {
        $conn = getDatabaseConnection();  //  Assuming $conn is your database connection

        $query = "SELECT c.*, u.username as sender_username 
                FROM communicate c
                JOIN web_users u ON c.sender_userid = u.userID
                WHERE (c.sender_userid = ? AND c.recipient_userid = ?) 
                    OR (c.sender_userid = ? AND c.recipient_userid = ?)
                ORDER BY c.message_date";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $senderId, $recipientId, $recipientId, $senderId);
        $stmt->execute();
        $result = $stmt->get_result();

        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        return $messages;
    }

    function storeChatMessage($senderId, $recipientId, $message) {
        // Get the database connection
        $dbConn = getDatabaseConnection();
    
        try {

            // Set the timezone to Jamaica
            $jamaicanTime = new DateTime("now", new DateTimeZone("America/Jamaica"));
            $currentTime = $jamaicanTime->format("Y-m-d H:i:s"); // Format to 'YYYY-MM-DD HH:MM:SS'
    
            $query = "INSERT INTO communicate (sender_userid, sender_username, recipient_userid, recipient_username, message_date, message_content)
                      VALUES (?, (SELECT username FROM web_users WHERE userID = ?), ?, (SELECT username FROM web_users WHERE userID = ?), ?, ?)";
            $stmt = $dbConn->prepare($query);
    
            if (!$stmt) {
                throw new mysqli_sql_exception("Error preparing statement: " . $dbConn->error);
            }
    
            $stmt->bind_param("iiiiss", $senderId, $senderId, $recipientId, $recipientId, $currentTime,$message);
    
            $stmt->execute();
    
            // Check for errors after execution
            if ($stmt->errno) {
                throw new mysqli_sql_exception("Error executing statement: " . $stmt->error);
            }
    
            return ['success' => true];
        } catch (mysqli_sql_exception $e) {
            return ['error' => $e->getMessage()];
        } finally {
            // Cleanup resources
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($dbConn) && $dbConn instanceof mysqli) {
                $dbConn->close();
            }
        }
    }


    
    function getPatientsForSupportUser($loggedInUsername) {
        // Get the database connection
        $dbConn = getDatabaseConnection();

        try {
            // Call the stored procedure
            $stmt = $dbConn->prepare("CALL GetPatientsForSupportUser(?)");
            $stmt->bind_param("s", $loggedInUsername);
            $stmt->execute();

            // Fetch results
            $result = $stmt->get_result();
            $patients = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $patients[] = $row;
                }
            }

            return $patients;
        } catch (mysqli_sql_exception $e) {
            return ['error' => $e->getMessage()];
        } finally {
            if (isset($stmt) && $stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
            if (isset($dbConn) && $dbConn instanceof mysqli) {
                $dbConn->close();
            }
        }
    }



    function getFamilyChatMessages(string $loggedInUsername,string $patientUsername) {
        // Get the database connection
        $conn = getDatabaseConnection();
        if (!$conn) {
            return ["error" => "Failed to connect to the database"];
        }
    
        // Call the stored procedure
        $query = "CALL GetFamilyChatMessages(?,?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $loggedInUsername,$patientUsername);
    
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $messages = [];
    
            // Fetch all results
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
    
            $stmt->close();
            $conn->close();
    
            // Return the list of messages or an error message
            return $messages;
        } else {
            $stmt->close();
            $conn->close();
            return ["error" => "Failed to execute the stored procedure"];
        }
    }



    function addFamilyChatMessage( string $senderUsername, int $senderId, string $content, string $patientUsername, int $patientId) {

        $conn = getDatabaseConnection();
        
        if (!$conn) {
            return ["error" => "Failed to connect to the database"];
        }

        try {
            
            // Set the timezone to Jamaica
            $jamaicanTime = new DateTime("now", new DateTimeZone("America/Jamaica"));
            $currentTime = $jamaicanTime->format("Y-m-d H:i:s"); // Format to 'YYYY-MM-DD HH:MM:SS'

            // Construct the SQL query
            $query = "INSERT INTO family_chat (sender_username, sender_id, message_date, content, patient_username, patient_id)
                    VALUES (?, ?, ?, ?, ?, ?)";

            // Prepare the query
            $stmt = $conn->prepare($query);

            // Bind the parameters
            $stmt->bind_param(
                "sisssi",
                $senderUsername,
                $senderId,
                $currentTime,
                $content,
                $patientUsername,
                $patientId
            );

            // Execute the query
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                return ["success" => "Message added successfully"];
            } else {
                $stmt->close();
                $conn->close();
                return ["error" => "Failed to add message: " . $stmt->error];
            }
        } catch (Exception $e) {
            // Handle errors (e.g., log them)
            error_log("Error: " . $e->getMessage());
            return ["error" => "Database error: " . $e->getMessage()];
        }
    }

    

    function getUserDetailsByUsername() {
        $conn = getDatabaseConnection(); // Assuming getDatabaseConnection() is defined elsewhere

        $query = "SELECT CONCAT(fname, ' ', lname) AS full_name,
                        gender,
                        email,
                        dob,
                        phone_number
                FROM web_users
                WHERE username = ?";

        $stmt = $conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("s", $_SESSION["loggedIn_username"]);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $userDetails = $result->fetch_assoc();
            } else {
                $userDetails = null; // User not found
            }

            $stmt->close();
        } else {
            // Handle prepare error (log it, display a message, etc.)
            $userDetails = false;
        }

        $conn->close();
        return $userDetails;
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


