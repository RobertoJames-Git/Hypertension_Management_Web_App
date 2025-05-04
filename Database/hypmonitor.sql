-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 03, 2025 at 04:48 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hypmonitor`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ActivateUser` (IN `p_token` VARCHAR(100))   BEGIN
    DECLARE tokenExists INT;
    DECLARE accountActive INT;
    DECLARE tokenValid INT;

    -- Check if the token exists
    SELECT COUNT(*) INTO tokenExists 
    FROM web_users 
    WHERE token = p_token;

    -- Check if the account is already active
    SELECT COUNT(*) INTO accountActive 
    FROM web_users 
    WHERE token = p_token AND account_status = 'active';

    -- Check if the token is valid (not expired and account is still pending)
    SELECT COUNT(*) INTO tokenValid 
    FROM web_users 
    WHERE token = p_token 
    AND account_status = 'pending' 
    AND token_expiration > NOW();

    -- Case 1: Token does not exist
    IF tokenExists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid token';

    -- Case 2: Account is already active
    ELSEIF accountActive > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Account is already active.';

    -- Case 3: Token exists but is expired
    ELSEIF tokenValid = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Activation link expired. A new one has been sent to your email.';

    -- Case 4: Token is valid, account is pending, and within time limit → Activate the account
    ELSE
        UPDATE web_users
        SET account_status = 'active'
        WHERE token = p_token;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddBloodPressureReading` (IN `p_username` VARCHAR(50), IN `p_reading_date` DATE, IN `p_reading_time` TIME, IN `p_systolic` INT, IN `p_diastolic` INT, IN `p_heart_rate` INT)   BEGIN
    DECLARE user_exists INT;
    DECLARE reading_exists INT;
    DECLARE user_id INT;
    
    -- Check if the username exists in the patient table and get the userID
    SELECT userid INTO user_id FROM patient WHERE username = p_username;
    SET user_exists = (user_id IS NOT NULL);

    IF user_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'User is not a registered patient';
    END IF;

    -- Check if a reading already exists for this user on the given date
    SELECT COUNT(*) INTO reading_exists FROM reading 
    WHERE userid = user_id AND username = p_username AND readingdate = p_reading_date;

    IF reading_exists > 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'A reading already exists for the selected date';
    END IF;

    -- Insert the new reading
    INSERT INTO reading (userid, username, readingdate, readingtime, systolic, diastolic, heart_rate)
    VALUES (user_id, p_username, p_reading_date, p_reading_time, p_systolic, p_diastolic, p_heart_rate);

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddFamilyChatMessage` (IN `senderUsername` VARCHAR(50), IN `senderId` INT, IN `messageContent` TEXT, IN `patientUsername` VARCHAR(50), IN `patientId` INT)   BEGIN
    -- Declare a variable to check if an accepted request exists
    DECLARE requestExists BOOLEAN;

    -- If sender and recipient are the same, bypass the check
    IF senderUsername = patientUsername AND senderId = patientId THEN
        SET requestExists = TRUE;
    ELSE
        -- Check if there is an accepted request between sender and recipient
        SELECT EXISTS (
            SELECT 1 
            FROM request 
            WHERE 
                ((sender_userid = senderId AND recipient_userid = patientId) OR
                 (sender_userid = patientId AND recipient_userid = senderId))
                AND request_status = 'accepted'
        ) INTO requestExists;
    END IF;

    -- If no request exists, raise an error
    IF NOT requestExists THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No accepted request exists between the specified users.';
    END IF;

    -- Insert the message into the family_chat table
    INSERT INTO family_chat (
        sender_username, 
        sender_id, 
        message_date, 
        content, 
        patient_username, 
        patient_id
    ) VALUES (
        senderUsername, 
        senderId, 
        NOW(), 
        messageContent, 
        patientUsername, 
        patientId
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddWebUser` (IN `p_fname` VARCHAR(50), IN `p_lname` VARCHAR(50), IN `p_gender` ENUM('male','female','other','rather not say'), IN `p_dob` DATE, IN `p_email` VARCHAR(200), IN `p_token` VARCHAR(150), IN `p_account_status` ENUM('pending','active'), IN `p_password_hashed` VARCHAR(100), IN `p_userType` ENUM('Hypertensive Individual','Family Member','Healthcare Professional'), IN `p_education_level` VARCHAR(50), IN `p_years_of_exp` ENUM('Less than a year','One to two years','Three to Fours years','Five years or more','Over a decade'), IN `p_phone_number` VARCHAR(15), OUT `p_generated_username` VARCHAR(50))   BEGIN
    DECLARE userExists INT;
    DECLARE newUserID INT;
    DECLARE p_username VARCHAR(10);
    DECLARE firstPart VARCHAR(3);
    DECLARE lastPart VARCHAR(3);
	DECLARE p_token_expiration datetime;
   
    -- Check if the email already exists
    SELECT COUNT(*) INTO userExists FROM web_users WHERE email = p_email;

    IF userExists = 0 THEN

        -- Set token expiration (10 minutes from now)
        SET p_token_expiration = NOW() + INTERVAL 10 MINUTE;
        -- Insert into web_users table without username
        INSERT INTO web_users (fname, lname, gender, dob, email, token, token_expiration,account_status, password,phone_number)
        VALUES (p_fname, p_lname, p_gender, p_dob, p_email, p_token,p_token_expiration,p_account_status, p_password_hashed,p_phone_number);

        -- Get the new userID
        SET newUserID = LAST_INSERT_ID();

        -- Extract the first 3 letters of fname and last 3 letters of lname (handling short names)
        SET firstPart = LEFT(p_fname, 3);
        SET lastPart = LEFT(p_lname, 3);

        -- Construct username
        SET p_username = CONCAT(firstPart, '_', lastPart, newUserID);
        SET p_generated_username =p_username;
        -- Update the user with the generated username
        UPDATE web_users SET username = p_username WHERE userID = newUserID;

        -- Insert into the appropriate table based on user type
        CASE 
            WHEN p_userType = 'Hypertensive Individual' THEN
                INSERT INTO patient (userid, username) VALUES (newUserID, p_username);
            WHEN p_userType = 'Family Member' THEN
                INSERT INTO family_member (userid, username, education_level) VALUES (newUserID, p_username, p_education_level);
            WHEN p_userType = 'Healthcare Professional' THEN
                INSERT INTO health_care_prof (userid, username, years_of_exp) 
                VALUES (newUserID, p_username,p_years_of_exp);
            ELSE SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Invalid userType';
        END CASE;
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Email already exists.';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CheckPatientReading` (IN `patientUsername` VARCHAR(50), IN `systolic` INT, IN `diastolic` INT)   BEGIN
    DECLARE patientUserID INT;
    DECLARE patientName VARCHAR(100);
    DECLARE minSystolic INT;
    DECLARE maxSystolic INT;
    DECLARE minDiastolic INT;
    DECLARE maxDiastolic INT;
    DECLARE recordExists INT;

    -- Default hypertension thresholds
    DECLARE defaultHypertensionMinSystolic INT DEFAULT 130;
    DECLARE defaultHypertensionMaxSystolic INT DEFAULT 139;
    DECLARE defaultHypertensionMinDiastolic INT DEFAULT 80;
    DECLARE defaultHypertensionMaxDiastolic INT DEFAULT 89;

    -- Retrieve the patient's userID and full name
    SELECT userid, CONCAT(fname, ' ', lname) INTO patientUserID, patientName
    FROM web_users
    WHERE username = patientUsername;

    -- Check if a record exists in the patient_range table for the patient
    SELECT COUNT(*) INTO recordExists
    FROM patient_range
    WHERE patient_userid = patientUserID AND patient_username = patientUsername;

    -- If no record exists in the patient_range table, check against default hypertension readings
    IF recordExists = 0 THEN
        IF (systolic >= defaultHypertensionMinSystolic AND systolic <= defaultHypertensionMaxSystolic) OR
           (diastolic >= defaultHypertensionMinDiastolic AND diastolic <= defaultHypertensionMaxDiastolic) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Patient readings indicate potential hypertension based on default thresholds.';
        ELSE
            SELECT 'Readings are within safe levels based on default thresholds.' AS message;
        END IF;
    ELSE
        -- Retrieve the patient's recommended readings from the patient_range table
        SELECT min_systolic, max_systolic, min_diastolic, max_diastolic
        INTO minSystolic, maxSystolic, minDiastolic, maxDiastolic
        FROM patient_range
        WHERE patient_userid = patientUserID AND patient_username = patientUsername;

        -- Check if the readings are out of range
        IF (systolic < minSystolic OR systolic > maxSystolic OR
            diastolic < minDiastolic OR diastolic > maxDiastolic) THEN
            -- Return the email and phone number of the patient's support network, along with the patient's name and recommended readings
            SELECT wu.email, wu.phone_number, patientName AS Patient_Name,
                   minSystolic AS Recommended_Min_Systolic,
                   maxSystolic AS Recommended_Max_Systolic,
                   minDiastolic AS Recommended_Min_Diastolic,
                   maxDiastolic AS Recommended_Max_Diastolic
            FROM web_users wu
            JOIN request r
              ON (r.sender_userid = patientUserID AND r.sender_username = patientUsername AND r.recipient_userid = wu.userID AND r.recipient_username = wu.username
                  OR r.recipient_userid = patientUserID AND r.recipient_username = patientUsername AND r.sender_userid = wu.userID AND r.sender_username = wu.username)
            WHERE r.request_status = 'accepted';
        ELSE
            -- If readings are in range, return a success message with patient's name and recommended readings
            SELECT 'Readings are within range.' AS message;
        END IF;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `deleteRejectedRequest` (IN `p_request_id` INT)   BEGIN
    DELETE FROM request
    WHERE request_id = p_request_id
      AND request_status = 'rejected';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAcceptedHealthCareProfessionalsByUsername` (IN `loggedInUsername` VARCHAR(50))   BEGIN
    -- Check if the user is a patient using their username
    IF EXISTS (SELECT 1 FROM patient WHERE username = loggedInUsername) THEN
        -- Retrieve healthcare professionals associated with accepted requests
        SELECT 
            web_users.userID, 
            web_users.username,
            web_users.fname,  -- Include first name
            web_users.lname   -- Include last name
        FROM web_users
        INNER JOIN request 
            ON (
                (request.sender_username = loggedInUsername AND request.recipient_userid = web_users.userID AND request.request_status = 'accepted')
                OR 
                (request.recipient_username = loggedInUsername AND request.sender_userid = web_users.userID AND request.request_status = 'accepted')
            )
        INNER JOIN health_care_prof 
            ON health_care_prof.userid = web_users.userID;
    ELSE
        -- Return a message if the user is not a patient
        SELECT "Error: User is not a patient" AS error_message;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getAcceptedPatients` (IN `p_loggedInUsername` VARCHAR(50))   BEGIN
    DECLARE user_role VARCHAR(50);
    DECLARE user_exists INT;

    -- Check if the logged-in user is a Family Member or Health Care Professional
    IF EXISTS (SELECT 1 FROM family_member WHERE username = p_loggedInUsername) THEN
        SET user_role = 'Family Member';
    ELSEIF EXISTS (SELECT 1 FROM health_care_prof WHERE username = p_loggedInUsername) THEN
        SET user_role = 'Health Care Professional';
    ELSE
        -- Raise an error if the user is neither a Family Member nor Health Care Professional
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'You are not authorized to run this procedure.';
    END IF;

    -- Retrieve patients where the logged-in user is the sender and the request is accepted
    SELECT 
        pt.username AS patient_username
    FROM request r
    INNER JOIN patient pt ON r.recipient_username = pt.username
    WHERE r.sender_username = p_loggedInUsername
      AND r.request_status = 'accepted'

    UNION

    -- Retrieve patients where the logged-in user is the recipient and the request is accepted
    SELECT 
        pt.username AS patient_username
    FROM request r
    INNER JOIN patient pt ON r.sender_username = pt.username
    WHERE r.recipient_username = p_loggedInUsername
      AND r.request_status = 'accepted';

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetBloodPressureReadings` (IN `p_username` VARCHAR(50), IN `p_page` INT, IN `p_limit` INT)   BEGIN
    DECLARE user_exists INT;
    DECLARE user_id INT;
    DECLARE offset_value INT;
    DECLARE total_records INT;
    DECLARE records_in_period INT;

    -- Calculate offset for pagination based on the user-specified limit
    SET offset_value = (p_page - 1) * p_limit;

    -- Check if the username exists in the patient table and get the userID
    SELECT userid INTO user_id FROM patient WHERE username = p_username;
    SET user_exists = (user_id IS NOT NULL);

    IF user_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'User is not a registered patient';
    END IF;

    -- Check if the user has ever entered any readings at all
    SELECT COUNT(*) INTO total_records FROM reading WHERE userid = user_id;

    IF total_records = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No records found. You may not have entered any data yet.';
    END IF;

    -- Check if the offset exceeds total records (i.e., no more pages available)
    IF offset_value >= total_records THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No more records available';
    END IF;

    -- Check if there are records in the requested period (user-specified limit)
    SELECT COUNT(*) INTO records_in_period 
    FROM (SELECT readingdate FROM reading WHERE userid = user_id ORDER BY readingdate DESC LIMIT p_limit OFFSET offset_value) AS subquery;

    IF records_in_period = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No records found for that period';
    END IF;

    -- Retrieve the readings for the requested period
    SELECT readingdate, readingtime, systolic, diastolic, heart_rate
    FROM reading 
    WHERE userid = user_id
    ORDER BY readingdate DESC
    LIMIT p_limit OFFSET offset_value;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetFamilyChatMessages` (IN `p_loggedInUsername` VARCHAR(50), IN `p_patientUsername` VARCHAR(50))   BEGIN
    DECLARE user_role VARCHAR(20);

    -- Check if the logged-in user is a patient or family member
    IF EXISTS (SELECT 1 FROM patient WHERE username = p_loggedInUsername) THEN
        SET user_role = 'Patient';
    ELSEIF EXISTS (SELECT 1 FROM family_member WHERE username = p_loggedInUsername) THEN
        SET user_role = 'Family Member';
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'User is not authorized to view family chat messages.';
    END IF;

    -- Retrieve messages for the specified patient
    SELECT
        fc.content AS message_content,
        fc.message_date,
        wu.username AS sender_username
    FROM family_chat fc
    JOIN web_users wu ON fc.sender_id = wu.userID
    WHERE fc.patient_username = p_patientUsername
    ORDER BY fc.message_date;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMatchingUsers` (IN `p_username_prefix` VARCHAR(50), IN `p_account_type` VARCHAR(30), IN `p_logged_in_username` VARCHAR(50))   BEGIN
    -- Validate the account type
    IF p_account_type NOT IN ('Family member', 'Health Care Professional', 'Patient') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid account type. Must be "Family member", "Health Care Professional", or "Patient".';
    END IF;

    -- Fetch users based on the account type and username prefix, excluding blocked users
    IF p_account_type = 'Family member' THEN
        SELECT
            fm.username AS family_member_username,
            CASE
                -- Return accepted if logged-in user is the sender or receiver of an accepted request
                WHEN EXISTS (
                    SELECT 1
                    FROM request
                    WHERE request.sender_username = p_logged_in_username
                        AND request.recipient_username = fm.username
                        AND request.request_status = 'accepted'
                ) OR EXISTS (
                    SELECT 1
                    FROM request
                    WHERE request.recipient_username = p_logged_in_username
                        AND request.sender_username = fm.username
                        AND request.request_status = 'accepted'
                ) THEN 'accepted'
                -- Return other statuses or no request
                WHEN EXISTS (
                    SELECT 1
                    FROM request
                    WHERE request.sender_username = p_logged_in_username
                        AND request.recipient_username = fm.username
                        AND request.request_status = 'pending'
                ) THEN 'pending'
                ELSE 'No Request Sent'
            END AS request_status
        FROM family_member fm
        WHERE fm.username LIKE CONCAT(p_username_prefix, '%')
          AND fm.username NOT IN (
              -- Users blocked by the logged-in user
              SELECT recipient_username
              FROM request
              WHERE sender_username = p_logged_in_username
                AND request_status = 'rejected'
              UNION
              -- Users who have blocked the logged-in user
              SELECT sender_username
              FROM request
              WHERE recipient_username = p_logged_in_username
                AND request_status = 'rejected'
          );

    ELSEIF p_account_type = 'Health Care Professional' THEN
        SELECT
            hp.username AS healthcare_prof_username,
            CASE
                -- Return accepted if logged-in user is the sender or receiver of an accepted request
                WHEN EXISTS (
                    SELECT 1
                    FROM request
                    WHERE request.sender_username = p_logged_in_username
                        AND request.recipient_username = hp.username
                        AND request.request_status = 'accepted'
                ) OR EXISTS (
                    SELECT 1
                    FROM request
                    WHERE request.recipient_username = p_logged_in_username
                        AND request.sender_username = hp.username
                        AND request.request_status = 'accepted'
                ) THEN 'accepted'
                -- Return other statuses or no request
                WHEN EXISTS (
                    SELECT 1
                    FROM request
                    WHERE request.sender_username = p_logged_in_username
                        AND request.recipient_username = hp.username
                        AND request.request_status = 'pending'
                ) THEN 'pending'
                ELSE 'No Request Sent'
            END AS request_status
        FROM health_care_prof hp
        WHERE hp.username LIKE CONCAT(p_username_prefix, '%')
           AND hp.username NOT IN (
              -- Users blocked by the logged-in user
              SELECT recipient_username
              FROM request
              WHERE sender_username = p_logged_in_username
                AND request_status = 'rejected'
              UNION
              -- Users who have blocked the logged-in user
              SELECT sender_username
              FROM request
              WHERE recipient_username = p_logged_in_username
                AND request_status = 'rejected'
          );

    ELSEIF p_account_type = 'Patient' THEN
        SELECT
            pt.username AS patient_username,
            CASE
                -- Return accepted if logged-in user is the sender or receiver of an accepted request
                WHEN EXISTS (
                    SELECT 1
                    FROM request
                    WHERE request.sender_username = p_logged_in_username
                        AND request.recipient_username = pt.username
                        AND request.request_status = 'accepted'
                ) OR EXISTS (
                    SELECT 1
                    FROM request
                    WHERE request.recipient_username = p_logged_in_username
                        AND request.sender_username = pt.username
                        AND request.request_status = 'accepted'
                ) THEN 'accepted'
                -- Return other statuses or no request
                WHEN EXISTS (
                    SELECT 1
                    FROM request
                    WHERE request.sender_username = p_logged_in_username
                        AND request.recipient_username = pt.username
                        AND request.request_status = 'pending'
                ) THEN 'pending'
                ELSE 'No Request Sent'
            END AS request_status
        FROM patient pt
        WHERE pt.username LIKE CONCAT(p_username_prefix, '%')
           AND pt.username NOT IN (
              -- Users blocked by the logged-in user
              SELECT recipient_username
              FROM request
              WHERE sender_username = p_logged_in_username
                AND request_status = 'rejected'
              UNION
              -- Users who have blocked the logged-in user
              SELECT sender_username
              FROM request
              WHERE recipient_username = p_logged_in_username
                AND request_status = 'rejected'
          );
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetPatientPendingRequests` (IN `p_patient_username` VARCHAR(50))   BEGIN
    -- Fetch pending requests from the monitor table (Healthcare Professionals)
    SELECT 
        'Healthcare Professional' AS sender_role,
        healthcare_prof_username AS sender_username,
        DATE_FORMAT(start_date, '%b %e, %Y') AS formatted_request_date,
        'pending' AS request_status
    FROM monitor
    WHERE patient_username = p_patient_username
      AND end_date IS NULL -- Assuming pending requests have no end date

    UNION ALL

    -- Fetch pending requests from the support table (Family Members)
    SELECT 
        'Family Member' AS sender_role,
        family_username AS sender_username,
        DATE_FORMAT(start_date, '%b %e, %Y') AS formatted_request_date,
        'pending' AS request_status
    FROM support
    WHERE patient_username = p_patient_username
      AND end_date IS NULL; -- Assuming pending requests have no end date
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetPatientsForSupportUser` (IN `loggedInUsername` VARCHAR(50))   BEGIN
    -- Check if the user is a health care professional or family member
    IF EXISTS (SELECT 1 FROM health_care_prof WHERE username = loggedInUsername) OR 
       EXISTS (SELECT 1 FROM family_member WHERE username = loggedInUsername) THEN

        -- Retrieve patients where the logged-in user is the sender and the request is accepted
        SELECT 
            pt.username AS patient_username
        FROM request r
        INNER JOIN patient pt ON r.recipient_username = pt.username
        WHERE r.sender_username = loggedInUsername
          AND r.request_status = 'accepted'

        UNION

        -- Retrieve patients where the logged-in user is the recipient and the request is accepted
        SELECT 
            pt.username AS patient_username
        FROM request r
        INNER JOIN patient pt ON r.sender_username = pt.username
        WHERE r.recipient_username = loggedInUsername
          AND r.request_status = 'accepted';

    ELSE
        -- Return an error message if the user is not a health care professional or family member
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Error: User is not a health care professional or family member';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetPendingRequests` (IN `p_username` VARCHAR(50))   BEGIN
    -- Check if the provided username exists in the web_users table
    IF NOT EXISTS (SELECT 1 FROM web_users WHERE username = p_username) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid username. User does not exist.';
    END IF;

    -- Retrieve all pending requests where the username is the recipient
    SELECT 
        request_id,
        sender_username AS from_username,
        request_status,
        -- Format the date to "Jan 1, 2025"
        DATE_FORMAT(request_date, '%b %e, %Y') AS formatted_request_date
    FROM request
    WHERE recipient_username = p_username AND request_status = 'pending';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getRejectedRequests` (IN `p_user_loggedIn` VARCHAR(50))   BEGIN
    SELECT 
        request_id,
        sender_username AS Sender,
        recipient_username AS Recipient,
        request_status AS Status,
        request_date AS Date
    FROM 
        request
    WHERE 
        recipient_username = p_user_loggedIn
        AND request_status = 'rejected'
    ORDER BY 
        request_date DESC; -- Show the most recent requests first
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getSupportNetwork` (IN `p_loggedInUsername` VARCHAR(50))   BEGIN
    -- Fetch accepted connections for the logged-in user (both sent and received)
    SELECT 
        r.sender_username AS support_username,
        CASE
            WHEN EXISTS (SELECT 1 FROM family_member WHERE username = r.sender_username) THEN 'Family Member'
            WHEN EXISTS (SELECT 1 FROM health_care_prof WHERE username = r.sender_username) THEN 'Healthcare Professional'
            WHEN EXISTS (SELECT 1 FROM patient WHERE username = r.sender_username) THEN 'Patient'
        END AS role,
        r.request_date AS connection_date
    FROM request r
    WHERE r.recipient_username = p_loggedInUsername AND r.request_status = 'accepted'

    UNION ALL

    SELECT 
        r.recipient_username AS support_username,
        CASE
            WHEN EXISTS (SELECT 1 FROM family_member WHERE username = r.recipient_username) THEN 'Family Member'
            WHEN EXISTS (SELECT 1 FROM health_care_prof WHERE username = r.recipient_username) THEN 'Healthcare Professional'
            WHEN EXISTS (SELECT 1 FROM patient WHERE username = r.recipient_username) THEN 'Patient'
        END AS role,
        r.request_date AS connection_date
    FROM request r
    WHERE r.sender_username = p_loggedInUsername AND r.request_status = 'accepted'

    ORDER BY connection_date DESC; -- Order by most recent connection first
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserCredentialsAndType` (IN `p_username` VARCHAR(50))   BEGIN
    DECLARE userPassword VARCHAR(100);
    DECLARE accountStatus ENUM('pending', 'active');
    DECLARE userType VARCHAR(50);

    -- Check if the username exists, retrieve the password, and account status
    SELECT password, account_status INTO userPassword, accountStatus
    FROM web_users 
    WHERE username = p_username;

    -- Check if the username exists
    IF userPassword IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid username and/or password';
    ELSEIF accountStatus = 'pending' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Account is not activated';
    ELSE
        -- Determine the user type by checking which table the username exists in
        IF EXISTS (SELECT 1 FROM patient WHERE username = p_username) THEN
            SET userType = 'Patient';
        ELSEIF EXISTS (SELECT 1 FROM family_member WHERE username = p_username) THEN
            SET userType = 'Family Member';
        ELSEIF EXISTS (SELECT 1 FROM health_care_prof WHERE username = p_username) THEN
            SET userType = 'Health Care Professional';
        ELSE
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'User type not found.';
        END IF;

        -- Return the password and user type
        SELECT userPassword AS password, userType AS user_type;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserPendingRequests` (IN `p_username` VARCHAR(50))   BEGIN
    DECLARE user_role VARCHAR(50);
    DECLARE user_id INT;

    -- Determine the user's role and user ID
    IF EXISTS (SELECT 1 FROM patient WHERE username = p_username) THEN
        SET user_role = 'Patient';
        SELECT userid INTO user_id FROM patient WHERE username = p_username;
    ELSEIF EXISTS (SELECT 1 FROM family_member WHERE username = p_username) THEN
        SET user_role = 'Family Member';
        SELECT userid INTO user_id FROM family_member WHERE username = p_username;
    ELSEIF EXISTS (SELECT 1 FROM health_care_prof WHERE username = p_username) THEN
        SET user_role = 'Health Care Professional';
        SELECT userid INTO user_id FROM health_care_prof WHERE username = p_username;
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid username or user does not exist.';
    END IF;

    -- Return pending requests based on the user's role
    CASE user_role
        WHEN 'Patient' THEN
            -- Retrieve pending requests for a patient
            SELECT 
                'Support Request' AS request_type,
                family_username AS from_username,
                start_date,
                support_status
            FROM support
            WHERE patient_userid = user_id AND support_status = 'pending'
            UNION ALL
            SELECT 
                'Monitor Request' AS request_type,
                healthcare_prof_username AS from_username,
                start_date,
                monitor_status AS support_status
            FROM monitor
            WHERE patient_userid = user_id AND monitor_status = 'pending';

        WHEN 'Family Member' THEN
            -- Retrieve pending requests sent by a family member
            SELECT 
                'Support Request' AS request_type,
                patient_username AS to_username,
                start_date,
                support_status
            FROM support
            WHERE family_member_userid = user_id AND support_status = 'pending';

        WHEN 'Health Care Professional' THEN
            -- Retrieve pending monitor requests sent to the healthcare professional
            SELECT 
                'Monitor Request' AS request_type,
                patient_username AS to_username,
                start_date,
                monitor_status AS support_status
            FROM monitor
            WHERE healthcare_prof_userid = user_id AND monitor_status = 'pending';

    END CASE;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `managePendingRequest` (IN `p_sender_username` VARCHAR(50), IN `p_user_loggedIn` VARCHAR(50), IN `p_decision` ENUM('accepted','rejected'))   BEGIN
    DECLARE v_loggedInUserId INT;
    DECLARE v_isPatient INT DEFAULT 0;
    DECLARE v_isSenderHCP INT DEFAULT 0;
    DECLARE v_existingHcpConnection INT DEFAULT 0;
    DECLARE v_request_id INT;

    -- 1. Check if the logged-in user exists and is a patient
    SELECT wu.userID INTO v_loggedInUserId
    FROM web_users wu
    WHERE wu.username = p_user_loggedIn;

    IF v_loggedInUserId IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Logged-in user does not exist.';
    END IF;

    SELECT COUNT(*) INTO v_isPatient
    FROM patient p
    WHERE p.userid = v_loggedInUserId;

    IF v_isPatient = 0 THEN
        -- If the user is not a patient, they might be accepting a request *from* a patient.
        -- We'll allow the procedure to continue for now, but the HCP check below won't apply.
        -- If you *only* want patients to manage requests via this procedure,
        -- you could uncomment the following line:
        -- SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only patients can manage requests this way.';
        SET v_isPatient = 0; -- Explicitly set for clarity in later logic
    ELSE
        SET v_isPatient = 1; -- Explicitly set for clarity
    END IF;

    -- 2. Check if the sender is a Health Care Professional
    SELECT COUNT(*) INTO v_isSenderHCP
    FROM health_care_prof hcp
    JOIN web_users wu ON hcp.userid = wu.userID
    WHERE wu.username = p_sender_username;

    -- 3. If logged-in user is a patient, sender is HCP, and decision is 'accepted',
    --    check if the patient already has an accepted HCP connection.
    IF v_isPatient = 1 AND v_isSenderHCP = 1 AND p_decision = 'accepted' THEN
        SELECT COUNT(*) INTO v_existingHcpConnection
        FROM request r
        JOIN health_care_prof hcp ON (r.sender_username = hcp.username OR r.recipient_username = hcp.username) -- The other party is an HCP
        WHERE r.request_status = 'accepted'
          AND (r.sender_username = p_user_loggedIn OR r.recipient_username = p_user_loggedIn) -- Involves the logged-in patient
          AND hcp.username != p_sender_username; -- Exclude the current request being accepted

        IF v_existingHcpConnection > 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'A patient can only have one health care professional in their support network.';
        END IF;
    END IF;

    -- Original Logic: Check if the specific pending request exists
    SELECT request_id INTO v_request_id
    FROM request
    WHERE sender_username = p_sender_username
      AND recipient_username = p_user_loggedIn
      AND request_status = 'pending';


    -- Case 1: Request does not exist
    IF v_request_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Pending request does not exist.';
    END IF;

    -- Case 2: Update the request status based on the decision
    UPDATE request
    SET request_status = p_decision,
        request_date = NOW() -- Optionally update the date when accepted/rejected
    WHERE request_id = v_request_id;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `PopulateSupportTable` (IN `p_patient_username` VARCHAR(50), IN `p_family_username` VARCHAR(50), IN `p_start_date` DATETIME)   BEGIN
    DECLARE patient_user_id INT;
    DECLARE family_user_id INT;
    DECLARE existing_request INT;

    -- Check if the patient username is valid and get their userID
    SELECT userid INTO patient_user_id 
    FROM patient 
    WHERE username = p_patient_username;

    IF patient_user_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid patient username.';
    END IF;

    -- Check if the family member username is valid and get their userID
    SELECT userid INTO family_user_id 
    FROM family_member 
    WHERE username = p_family_username;

    IF family_user_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid family member username.';
    END IF;

    -- Check if a record already exists in the support table
    SELECT COUNT(*) INTO existing_request 
    FROM support 
    WHERE patient_userid = patient_user_id 
      AND family_member_userid = family_user_id;

    -- If the record exists, delete it. Otherwise, insert a new one.
    IF existing_request > 0 THEN
        DELETE FROM support 
        WHERE patient_userid = patient_user_id 
          AND family_member_userid = family_user_id;
    ELSE
        INSERT INTO support (family_member_userid, family_username, patient_userid, patient_username, start_date, support_status)
        VALUES (family_user_id, p_family_username, patient_user_id, p_patient_username, p_start_date, 'pending');
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `removeSupportNetwork` (IN `p_loggedInUsername` VARCHAR(50), IN `p_supportUsername` VARCHAR(50))   BEGIN
    DELETE FROM request
    WHERE request_status = 'accepted'
      AND (
            (sender_username = p_loggedInUsername AND recipient_username = p_supportUsername)
         OR (sender_username = p_supportUsername AND recipient_username = p_loggedInUsername)
      );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sendRequest` (IN `p_sender_username` VARCHAR(50), IN `p_recipient_username` VARCHAR(50), IN `p_current_date` DATETIME)   BEGIN
    DECLARE sender_userid INT;
    DECLARE recipient_userid INT;
    DECLARE sender_role ENUM('Health Care Professional', 'Patient', 'Family Member');
    DECLARE recipient_role ENUM('Health Care Professional', 'Patient', 'Family Member');
    DECLARE existing_request_id INT;
    DECLARE existing_request_status ENUM('pending', 'accepted', 'rejected');
    DECLARE existing_sender_username VARCHAR(50);

    -- Labeled block to allow the use of LEAVE
    procedure_end: BEGIN
        -- Determine sender role and user ID
        SELECT userid INTO sender_userid 
        FROM web_users 
        WHERE username = p_sender_username;

        IF EXISTS (SELECT 1 FROM health_care_prof WHERE userid = sender_userid) THEN
            SET sender_role = 'Health Care Professional';
        ELSEIF EXISTS (SELECT 1 FROM patient WHERE userid = sender_userid) THEN
            SET sender_role = 'Patient';
        ELSEIF EXISTS (SELECT 1 FROM family_member WHERE userid = sender_userid) THEN
            SET sender_role = 'Family Member';
        ELSE
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid sender: Must be a healthcare professional, patient, or family member.';
        END IF;

        -- Determine recipient role and user ID
        SELECT userid INTO recipient_userid 
        FROM web_users 
        WHERE username = p_recipient_username;

        IF EXISTS (SELECT 1 FROM health_care_prof WHERE userid = recipient_userid) THEN
            SET recipient_role = 'Health Care Professional';
        ELSEIF EXISTS (SELECT 1 FROM patient WHERE userid = recipient_userid) THEN
            SET recipient_role = 'Patient';
        ELSEIF EXISTS (SELECT 1 FROM family_member WHERE userid = recipient_userid) THEN
            SET recipient_role = 'Family Member';
        ELSE
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid recipient: Must be a healthcare professional, patient, or family member.';
        END IF;

        -- Restrict requests between users of the same role
        IF sender_role = recipient_role THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Requests cannot be sent between users of the same role.';
        END IF;

        -- Check for existing request between the sender and recipient
        SELECT request_id, request_status, sender_username INTO existing_request_id, existing_request_status, existing_sender_username
        FROM request
        WHERE (sender_username = p_sender_username AND recipient_username = p_recipient_username)
           OR (sender_username = p_recipient_username AND recipient_username = p_sender_username);

        -- Handle the edge cases
        IF existing_request_id IS NOT NULL THEN
            -- Case 1: Modify existing request to "accepted" if roles are reversed
            IF existing_request_status = 'pending' AND existing_sender_username != p_sender_username THEN
                UPDATE request 
                SET request_status = 'accepted', request_date = p_current_date
                WHERE request_id = existing_request_id;

            -- Case 2: Cancel request if the same person sends another request
            ELSEIF existing_sender_username = p_sender_username THEN
                DELETE FROM request WHERE request_id = existing_request_id;

            -- Prevent further requests if already accepted
            ELSEIF existing_request_status = 'accepted' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Request already accepted';
            END IF;

            -- Exit the procedure
            LEAVE procedure_end;
        END IF;

        -- Insert new request if no existing record
        INSERT INTO request (
            sender_userid, sender_username, 
            recipient_userid, recipient_username, 
            request_status, request_date
        ) VALUES (
            sender_userid, p_sender_username, 
            recipient_userid, p_recipient_username, 
            'pending', p_current_date
        );

    END procedure_end;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `SetPatientRange` (IN `hcpUsername` VARCHAR(50), IN `patientUsername` VARCHAR(50), IN `minSystolic` INT, IN `maxSystolic` INT, IN `minDiastolic` INT, IN `maxDiastolic` INT)   BEGIN
    DECLARE hcpUserID INT;
    DECLARE patientUserID INT;
    DECLARE acceptedRequestExists INT;
    DECLARE recordExists INT;

    -- Retrieve HCP's userID from health_care_prof table
    SELECT userid INTO hcpUserID
    FROM health_care_prof
    WHERE username = hcpUsername;

    -- Retrieve Patient's userID from patient table
    SELECT userid INTO patientUserID
    FROM patient
    WHERE username = patientUsername;

    -- Check if an accepted request exists between the HCP and the patient
    SELECT COUNT(*) INTO acceptedRequestExists
    FROM request
    WHERE (sender_userid = hcpUserID AND sender_username = hcpUsername AND recipient_userid = patientUserID AND recipient_username = patientUsername)
       OR (sender_userid = patientUserID AND sender_username = patientUsername AND recipient_userid = hcpUserID AND recipient_username = hcpUsername)
       AND request_status = 'accepted';

    -- If no accepted request exists, signal an error
    IF acceptedRequestExists = 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'No accepted request exists between this HCP and the patient.';
    ELSE
        -- Check if a record already exists in patient_range table
        SELECT COUNT(*) INTO recordExists
        FROM patient_range
        WHERE hcp_userid = hcpUserID
          AND hcp_username = hcpUsername
          AND patient_userid = patientUserID
          AND patient_username = patientUsername;

        -- If record exists, update it; otherwise, insert a new one
        IF recordExists > 0 THEN
            UPDATE patient_range
            SET min_systolic = minSystolic,
                max_systolic = maxSystolic,
                min_diastolic = minDiastolic,
                max_diastolic = maxDiastolic,
                date_set = CURRENT_TIMESTAMP
            WHERE hcp_userid = hcpUserID
              AND hcp_username = hcpUsername
              AND patient_userid = patientUserID
              AND patient_username = patientUsername;
        ELSE
            INSERT INTO patient_range (
                patient_userid,
                patient_username,
                min_systolic,
                max_systolic,
                min_diastolic,
                max_diastolic,
                date_set,
                hcp_userid,
                hcp_username
            )
            VALUES (
                patientUserID,
                patientUsername,
                minSystolic,
                maxSystolic,
                minDiastolic,
                maxDiastolic,
                CURRENT_TIMESTAMP,
                hcpUserID,
                hcpUsername
            );
        END IF;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `StoreChatMessage` (IN `senderId` INT, IN `recipientId` INT, IN `messageContent` TEXT)   BEGIN
    -- Variable to track if an 'accepted' request exists
    DECLARE recordExists BOOLEAN;

    -- Check if there's an 'accepted' request between the sender and recipient
    SELECT EXISTS(
        SELECT 1 
        FROM request 
        WHERE 
            ((sender_userid = senderId AND recipient_userid = recipientId) OR
             (sender_userid = recipientId AND recipient_userid = senderId)) 
            AND request_status = 'accepted'
    ) INTO recordExists;

    -- If no record exists, throw an error
    IF NOT recordExists THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No accepted request exists between users.';
    END IF;

    -- If a record exists, insert the message into the 'communicate' table
    INSERT INTO communicate (
        sender_userid, sender_username, recipient_userid, recipient_username, message_date, message_content
    ) VALUES (
        senderId, 
        (SELECT username FROM web_users WHERE userID = senderId),
        recipientId, 
        (SELECT username FROM web_users WHERE userID = recipientId), 
        NOW(), 
        messageContent
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateUserTokenAndPassword` (IN `p_current_token` VARCHAR(150), IN `p_new_token` VARCHAR(150), IN `p_new_password_hashed` VARCHAR(100))   BEGIN
    DECLARE userExists INT;

    -- Check if a user exists with the given current token
    SELECT COUNT(*) INTO userExists FROM web_users WHERE token = p_current_token;

    IF userExists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid token. No matching user found.';
    ELSE
        -- Update the token, expiration, and password
        UPDATE web_users 
        SET 
            token = p_new_token,
            token_expiration = NOW() + INTERVAL 10 MINUTE,
            password = p_new_password_hashed
        WHERE token = p_current_token;

        -- Return the updated user details as a result set
        SELECT fname, lname, username, email
        FROM web_users 
        WHERE token = p_new_token;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `communicate`
--

CREATE TABLE `communicate` (
  `communicate_id` int(11) NOT NULL,
  `sender_userid` int(11) NOT NULL,
  `sender_username` varchar(50) NOT NULL,
  `recipient_userid` int(11) NOT NULL,
  `recipient_username` varchar(50) NOT NULL,
  `message_date` datetime NOT NULL,
  `message_content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `communicate`
--

INSERT INTO `communicate` (`communicate_id`, `sender_userid`, `sender_username`, `recipient_userid`, `recipient_username`, `message_date`, `message_content`) VALUES
(44, 7, 'Wil_Sam7', 1, 'Dav_Rob1', '2025-04-18 19:49:29', 'hello'),
(45, 1, 'Dav_Rob1', 7, 'Wil_Sam7', '2025-04-18 19:49:45', 'whats up doc?'),
(46, 7, 'Wil_Sam7', 1, 'Dav_Rob1', '2025-04-18 20:41:27', 'how have you been feeling?'),
(47, 1, 'Dav_Rob1', 7, 'Wil_Sam7', '2025-04-18 20:41:37', 'I feel great'),
(48, 16, 'Kay_Jac16', 1, 'Dav_Rob1', '2025-04-23 17:32:51', 'hello'),
(49, 1, 'Dav_Rob1', 16, 'Kay_Jac16', '2025-04-23 17:44:53', 'whats up'),
(50, 1, 'Dav_Rob1', 16, 'Kay_Jac16', '2025-04-23 17:45:14', '..'),
(51, 16, 'Kay_Jac16', 1, 'Dav_Rob1', '2025-04-23 19:08:42', 'I am doing fine'),
(52, 1, 'Dav_Rob1', 16, 'Kay_Jac16', '2025-04-23 19:09:05', 'I am doing fine too'),
(53, 16, 'Kay_Jac16', 1, 'Dav_Rob1', '2025-04-23 19:09:17', 'how are your readings?'),
(54, 1, 'Dav_Rob1', 16, 'Kay_Jac16', '2025-04-23 19:10:15', 'they have been good you can check them in the support page. But my meds are running low tho'),
(55, 15, 'Dia_Pot15', 16, 'Kay_Jac16', '2025-04-27 18:36:56', 'hello doctor'),
(56, 16, 'Kay_Jac16', 15, 'Dia_Pot15', '2025-04-27 18:37:08', 'how are you doing'),
(57, 7, 'Wil_Sam7', 10, 'Fre_Lew10', '2025-04-28 19:30:07', 'Hello'),
(58, 7, 'Wil_Sam7', 10, 'Fre_Lew10', '2025-04-28 19:31:38', 'hello'),
(59, 7, 'Wil_Sam7', 1, 'Dav_Rob1', '2025-04-28 20:39:27', 'I\'m williams'),
(60, 7, 'Wil_Sam7', 1, 'Dav_Rob1', '2025-04-28 20:50:15', '..'),
(61, 1, 'Dav_Rob1', 7, 'Wil_Sam7', '2025-04-28 20:50:21', 'jjj'),
(62, 7, 'Wil_Sam7', 1, 'Dav_Rob1', '2025-04-28 20:55:18', '...4'),
(63, 1, 'Dav_Rob1', 7, 'Wil_Sam7', '2025-04-28 20:55:26', '98j'),
(64, 7, 'Wil_Sam7', 1, 'Dav_Rob1', '2025-04-28 21:07:45', 'kkk'),
(65, 1, 'Dav_Rob1', 7, 'Wil_Sam7', '2025-04-28 21:07:51', 'yyy'),
(66, 1, 'Dav_Rob1', 7, 'Wil_Sam7', '2025-04-28 21:22:25', 'xbxbxb'),
(67, 7, 'Wil_Sam7', 1, 'Dav_Rob1', '2025-04-28 21:22:28', 'bxbx'),
(68, 1, 'Dav_Rob1', 7, 'Wil_Sam7', '2025-04-28 21:27:42', 'p'),
(69, 7, 'Wil_Sam7', 1, 'Dav_Rob1', '2025-04-28 21:27:47', 'f');

-- --------------------------------------------------------

--
-- Table structure for table `family_chat`
--

CREATE TABLE `family_chat` (
  `chat_id` int(11) NOT NULL,
  `sender_username` varchar(50) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message_date` datetime NOT NULL,
  `content` text NOT NULL,
  `patient_username` varchar(50) NOT NULL,
  `patient_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_chat`
--

INSERT INTO `family_chat` (`chat_id`, `sender_username`, `sender_id`, `message_date`, `content`, `patient_username`, `patient_id`) VALUES
(2, 'San_Ros2', 2, '2025-03-29 01:23:21', 'Hey family', 'Dav_Rob1', 1),
(3, 'Dav_Rob1', 1, '2025-03-29 21:01:46', 'Whats up?', 'Dav_Rob1', 1),
(4, 'Fre_Lew10', 10, '2025-03-29 22:20:13', 'hey yo', 'Fre_Lew10', 10),
(5, 'San_Ros2', 2, '2025-03-29 22:41:13', 'how you doing?', 'Fre_Lew10', 10),
(6, 'San_Ros2', 2, '2025-03-29 22:41:25', 'you fine?\n', 'Fre_Lew10', 10),
(7, 'San_Ros2', 2, '2025-03-29 22:41:33', 'one', 'Fre_Lew10', 10),
(8, 'Fre_Lew10', 10, '2025-03-29 22:41:44', 'I\'m okay', 'Fre_Lew10', 10),
(9, 'Fre_Lew10', 10, '2025-03-29 22:41:58', 'Thing are going well', 'Fre_Lew10', 10),
(10, 'San_Ros2', 2, '2025-03-29 22:43:23', 'i', 'Fre_Lew10', 10),
(11, 'San_Ros2', 2, '2025-03-29 22:43:30', 'k', 'Fre_Lew10', 10),
(12, 'San_Ros2', 2, '2025-03-29 22:43:35', 's', 'Fre_Lew10', 10),
(13, 'Fre_Lew10', 10, '2025-03-29 22:43:48', 'Stop sending soo many messages', 'Fre_Lew10', 10),
(14, 'Fre_Lew10', 10, '2025-03-29 22:43:55', '..', 'Fre_Lew10', 10),
(15, 'Nat_Dre8', 8, '2025-03-30 15:21:30', 'This looks fun\n', 'Fre_Lew10', 10),
(16, 'Nat_Dre8', 8, '2025-03-30 18:39:52', 'I see you\'re keeping on track with your readings', 'Dav_Rob1', 1),
(17, 'Dav_Rob1', 1, '2025-03-30 18:40:11', 'yes', 'Dav_Rob1', 1),
(20, 'Dav_Rob1', 1, '2025-04-18 20:40:02', 'My readings were great this week', 'Dav_Rob1', 1),
(21, 'Nat_Dre8', 8, '2025-04-18 20:40:50', 'Wonderful', 'Dav_Rob1', 1),
(22, 'Dav_Rob1', 1, '2025-04-23 16:58:50', 'hmmm..', 'Dav_Rob1', 1),
(23, 'Nat_Dre8', 8, '2025-04-24 21:20:50', 'hello, how was your day', 'Dav_Rob1', 1),
(24, 'Dav_Rob1', 1, '2025-04-24 21:21:07', 'i am doing fine', 'Dav_Rob1', 1),
(25, 'Nat_Dre8', 8, '2025-04-24 21:24:52', 'My readings are great', 'Dav_Rob1', 1),
(26, 'Dav_Rob1', 1, '2025-04-24 21:25:09', 'yeap', 'Dav_Rob1', 1),
(27, 'Nat_Dre8', 8, '2025-04-24 21:26:50', 'How are you\'re readings', 'Dav_Rob1', 1),
(28, 'Dav_Rob1', 1, '2025-04-24 21:27:31', 'you can check it ', 'Dav_Rob1', 1),
(29, 'Dav_Rob1', 1, '2025-04-24 21:31:10', 'okay', 'Dav_Rob1', 1),
(30, 'Nat_Dre8', 8, '2025-04-24 21:31:19', 'yea', 'Dav_Rob1', 1),
(31, 'Dav_Rob1', 1, '2025-04-24 21:33:07', 'tes', 'Dav_Rob1', 1),
(32, 'Nat_Dre8', 8, '2025-04-24 21:33:18', 'yeap', 'Dav_Rob1', 1),
(33, 'Dav_Rob1', 1, '2025-04-24 21:41:40', 'hwllo', 'Dav_Rob1', 1),
(34, 'Nat_Dre8', 8, '2025-04-24 21:41:53', '...', 'Dav_Rob1', 1),
(35, 'Dav_Rob1', 1, '2025-04-24 21:42:06', 'test time', 'Dav_Rob1', 1),
(36, 'Dav_Rob1', 1, '2025-04-24 21:42:54', 'hey', 'Dav_Rob1', 1),
(37, 'Nat_Dre8', 8, '2025-04-24 21:43:03', '...', 'Dav_Rob1', 1),
(38, 'Nat_Dre8', 8, '2025-04-24 21:46:41', 'testing remove', 'Dav_Rob1', 1),
(39, 'Nat_Dre8', 8, '2025-04-24 22:00:12', 'whats up', 'Fre_Lew10', 10),
(40, 'Fre_Lew10', 10, '2025-04-25 10:14:36', 'hello', 'Fre_Lew10', 10),
(41, 'Nat_Dre8', 8, '2025-04-25 10:22:48', '....', 'Fre_Lew10', 10),
(42, 'Dav_Rob1', 1, '2025-04-28 20:32:34', 'hello', 'Dav_Rob1', 1),
(43, 'San_Ros2', 2, '2025-04-28 20:32:45', 'whats up', 'Dav_Rob1', 1),
(44, 'San_Ros2', 2, '2025-04-28 20:33:05', 'hey', 'Dav_Rob1', 1),
(45, 'San_Ros2', 2, '2025-04-28 20:33:12', '...', 'Dav_Rob1', 1),
(46, 'San_Ros2', 2, '2025-04-28 20:51:02', 'jkwdjkw', 'Dav_Rob1', 1),
(47, 'San_Ros2', 2, '2025-04-28 22:30:25', 'jjj', 'Dav_Rob1', 1),
(48, 'Dav_Rob1', 1, '2025-04-28 22:36:52', 'jjj', 'Dav_Rob1', 1),
(49, 'San_Ros2', 2, '2025-04-28 22:37:18', 'jh', 'Dav_Rob1', 1),
(50, 'Dav_Rob1', 1, '2025-04-28 22:37:49', 'jjn', 'Dav_Rob1', 1),
(51, 'Dav_Rob1', 1, '2025-04-28 22:37:56', 'nnkn', 'Dav_Rob1', 1),
(52, 'Dav_Rob1', 1, '2025-04-28 22:38:07', 'oopp', 'Dav_Rob1', 1),
(53, 'San_Ros2', 2, '2025-04-28 22:39:28', 'lll', 'Dav_Rob1', 1);

-- --------------------------------------------------------

--
-- Table structure for table `family_member`
--

CREATE TABLE `family_member` (
  `userid` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `education_level` enum('No Formal Education','Elementary','Secondary','Some Tertiary','Vocational Training','Degree') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_member`
--

INSERT INTO `family_member` (`userid`, `username`, `education_level`) VALUES
(2, 'San_Ros2', 'Degree'),
(8, 'Nat_Dre8', 'Degree'),
(11, 'Ada_Ros11', 'Degree'),
(14, 'Fre_Smi14', 'No Formal Education');

-- --------------------------------------------------------

--
-- Table structure for table `health_care_prof`
--

CREATE TABLE `health_care_prof` (
  `userid` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `years_of_exp` enum('Less than a year','One to two years','Three to Four years','Five years or more','Over a decade') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_care_prof`
--

INSERT INTO `health_care_prof` (`userid`, `username`, `years_of_exp`) VALUES
(7, 'Wil_Sam7', 'Five years or more'),
(16, 'Kay_Jac16', 'Five years or more');

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `userid` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `hyp_status` enum('normal','high','low','critical') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`userid`, `username`, `hyp_status`) VALUES
(1, 'Dav_Rob1', NULL),
(10, 'Fre_Lew10', NULL),
(12, 'Gar_Fer12', NULL),
(13, 'Rog_bla13', NULL),
(15, 'Dia_Pot15', NULL),
(17, 'Kem_Chr17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `patient_range`
--

CREATE TABLE `patient_range` (
  `patient_userid` int(11) NOT NULL,
  `patient_username` varchar(50) NOT NULL,
  `min_systolic` int(11) NOT NULL,
  `max_systolic` int(11) NOT NULL,
  `min_diastolic` int(11) NOT NULL,
  `max_diastolic` int(11) NOT NULL,
  `date_set` datetime NOT NULL,
  `hcp_userid` int(11) NOT NULL,
  `hcp_username` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patient_range`
--

INSERT INTO `patient_range` (`patient_userid`, `patient_username`, `min_systolic`, `max_systolic`, `min_diastolic`, `max_diastolic`, `date_set`, `hcp_userid`, `hcp_username`) VALUES
(1, 'Dav_Rob1', 100, 140, 70, 80, '2025-05-02 21:45:12', 7, 'Wil_Sam7'),
(1, 'Dav_Rob1', 100, 140, 60, 80, '2025-04-27 22:04:27', 16, 'Kay_Jac16'),
(10, 'Fre_Lew10', 50, 120, 88, 90, '2025-04-27 12:09:57', 7, 'Wil_Sam7');

-- --------------------------------------------------------

--
-- Table structure for table `reading`
--

CREATE TABLE `reading` (
  `userid` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `readingdate` date NOT NULL,
  `readingtime` time NOT NULL,
  `systolic` int(11) NOT NULL,
  `diastolic` int(11) NOT NULL,
  `heart_rate` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reading`
--

INSERT INTO `reading` (`userid`, `username`, `readingdate`, `readingtime`, `systolic`, `diastolic`, `heart_rate`) VALUES
(1, 'Dav_Rob1', '2025-02-26', '09:08:00', 150, 90, 83),
(1, 'Dav_Rob1', '2025-02-27', '08:00:00', 140, 90, 80),
(1, 'Dav_Rob1', '2025-02-28', '08:00:00', 100, 90, 78),
(1, 'Dav_Rob1', '2025-03-01', '13:42:40', 120, 100, 90),
(1, 'Dav_Rob1', '2025-03-02', '01:00:52', 150, 95, 78),
(1, 'Dav_Rob1', '2025-03-03', '01:01:08', 160, 100, 82),
(1, 'Dav_Rob1', '2025-03-04', '01:01:34', 145, 100, 82),
(1, 'Dav_Rob1', '2025-03-05', '01:02:47', 170, 105, 90),
(1, 'Dav_Rob1', '2025-03-06', '01:03:02', 155, 98, 88),
(1, 'Dav_Rob1', '2025-03-07', '01:03:28', 165, 102, 80),
(1, 'Dav_Rob1', '2025-03-08', '01:04:00', 180, 110, 95),
(1, 'Dav_Rob1', '2025-03-10', '09:00:00', 130, 80, 80),
(1, 'Dav_Rob1', '2025-03-11', '09:00:00', 120, 100, 83),
(1, 'Dav_Rob1', '2025-03-12', '06:25:00', 180, 131, 105),
(1, 'Dav_Rob1', '2025-03-13', '09:00:00', 110, 80, 90),
(1, 'Dav_Rob1', '2025-03-14', '09:00:00', 108, 79, 90),
(1, 'Dav_Rob1', '2025-03-16', '14:05:00', 50, 100, 80),
(1, 'Dav_Rob1', '2025-03-17', '16:12:00', 300, 200, 80),
(1, 'Dav_Rob1', '2025-03-18', '04:00:00', 120, 70, 50),
(1, 'Dav_Rob1', '2025-03-19', '16:19:00', 100, 80, 60),
(1, 'Dav_Rob1', '2025-03-20', '16:19:00', 100, 80, 60),
(1, 'Dav_Rob1', '2025-03-21', '16:27:00', 80, 50, 50),
(1, 'Dav_Rob1', '2025-03-22', '16:27:00', 80, 50, 50),
(1, 'Dav_Rob1', '2025-03-23', '04:24:00', 80, 50, 50),
(1, 'Dav_Rob1', '2025-03-24', '04:24:00', 80, 50, 50),
(1, 'Dav_Rob1', '2025-03-25', '04:24:00', 80, 50, 50),
(1, 'Dav_Rob1', '2025-04-15', '14:09:00', 100, 80, 70),
(1, 'Dav_Rob1', '2025-04-16', '13:53:00', 300, 200, 120),
(1, 'Dav_Rob1', '2025-04-17', '18:12:00', 80, 50, 50),
(1, 'Dav_Rob1', '2025-04-18', '06:10:00', 287, 123, 60),
(1, 'Dav_Rob1', '2025-04-19', '10:04:00', 300, 200, 100),
(1, 'Dav_Rob1', '2025-04-20', '07:00:00', 150, 100, 80),
(1, 'Dav_Rob1', '2025-04-26', '04:27:00', 80, 50, 88),
(10, 'Fre_Lew10', '2025-03-18', '08:00:00', 130, 101, 90),
(10, 'Fre_Lew10', '2025-03-19', '09:00:00', 120, 100, 77),
(10, 'Fre_Lew10', '2025-03-20', '09:00:00', 118, 98, 75),
(15, 'Dia_Pot15', '2025-03-01', '08:30:00', 122, 78, 71),
(15, 'Dia_Pot15', '2025-03-02', '09:15:00', 138, 90, 77),
(15, 'Dia_Pot15', '2025-03-03', '07:45:00', 130, 85, 74),
(15, 'Dia_Pot15', '2025-03-04', '10:20:00', 118, 76, 69),
(15, 'Dia_Pot15', '2025-03-05', '06:50:00', 140, 95, 80),
(15, 'Dia_Pot15', '2025-03-06', '11:10:00', 125, 82, 72),
(15, 'Dia_Pot15', '2025-03-07', '07:25:00', 135, 89, 78),
(15, 'Dia_Pot15', '2025-03-08', '09:05:00', 120, 80, 70),
(15, 'Dia_Pot15', '2025-03-09', '08:40:00', 128, 83, 75),
(15, 'Dia_Pot15', '2025-03-10', '10:55:00', 145, 98, 82),
(15, 'Dia_Pot15', '2025-03-11', '06:30:00', 132, 88, 76),
(15, 'Dia_Pot15', '2025-03-12', '12:00:00', 122, 78, 71),
(15, 'Dia_Pot15', '2025-03-13', '07:10:00', 138, 92, 79),
(15, 'Dia_Pot15', '2025-03-14', '08:20:00', 126, 84, 73),
(15, 'Dia_Pot15', '2025-03-15', '09:30:00', 140, 97, 81),
(15, 'Dia_Pot15', '2025-03-16', '07:00:00', 130, 85, 74),
(15, 'Dia_Pot15', '2025-03-17', '08:10:00', 120, 79, 70),
(15, 'Dia_Pot15', '2025-03-18', '09:45:00', 135, 90, 78),
(15, 'Dia_Pot15', '2025-03-19', '06:55:00', 128, 82, 74),
(15, 'Dia_Pot15', '2025-03-20', '11:05:00', 145, 100, 83),
(15, 'Dia_Pot15', '2025-03-21', '07:35:00', 125, 80, 72),
(15, 'Dia_Pot15', '2025-03-22', '10:25:00', 138, 91, 79),
(15, 'Dia_Pot15', '2025-03-23', '08:45:00', 130, 87, 75),
(15, 'Dia_Pot15', '2025-03-24', '06:40:00', 118, 75, 69),
(15, 'Dia_Pot15', '2025-03-25', '12:10:00', 142, 96, 81),
(15, 'Dia_Pot15', '2025-03-26', '07:20:00', 126, 83, 73),
(15, 'Dia_Pot15', '2025-03-27', '09:15:00', 135, 89, 77),
(15, 'Dia_Pot15', '2025-03-28', '08:00:00', 120, 79, 71),
(15, 'Dia_Pot15', '2025-03-29', '10:30:00', 140, 97, 80),
(15, 'Dia_Pot15', '2025-03-30', '07:50:00', 128, 84, 75),
(15, 'Dia_Pot15', '2025-03-31', '09:55:00', 145, 100, 82),
(15, 'Dia_Pot15', '2025-04-01', '07:00:00', 120, 80, 72),
(15, 'Dia_Pot15', '2025-04-02', '09:15:00', 130, 85, 75),
(15, 'Dia_Pot15', '2025-04-03', '07:45:00', 125, 78, 70),
(15, 'Dia_Pot15', '2025-04-04', '10:20:00', 135, 90, 78),
(15, 'Dia_Pot15', '2025-04-05', '06:50:00', 118, 76, 69),
(15, 'Dia_Pot15', '2025-04-06', '11:10:00', 140, 95, 80),
(15, 'Dia_Pot15', '2025-04-07', '07:25:00', 128, 82, 74),
(15, 'Dia_Pot15', '2025-04-08', '09:05:00', 132, 88, 76),
(15, 'Dia_Pot15', '2025-04-09', '08:40:00', 120, 80, 71),
(15, 'Dia_Pot15', '2025-04-10', '10:55:00', 138, 92, 79),
(15, 'Dia_Pot15', '2025-04-11', '06:30:00', 126, 84, 73);

-- --------------------------------------------------------

--
-- Table structure for table `request`
--

CREATE TABLE `request` (
  `request_id` int(11) NOT NULL,
  `sender_userid` int(11) NOT NULL,
  `sender_username` varchar(50) NOT NULL,
  `recipient_userid` int(11) NOT NULL,
  `recipient_username` varchar(50) NOT NULL,
  `request_status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `request_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request`
--

INSERT INTO `request` (`request_id`, `sender_userid`, `sender_username`, `recipient_userid`, `recipient_username`, `request_status`, `request_date`) VALUES
(41, 11, 'Ada_Ros11', 15, 'Dia_Pot15', 'pending', '2025-04-17 17:41:29'),
(54, 14, 'Fre_Smi14', 15, 'Dia_Pot15', 'accepted', '2025-04-21 19:28:05'),
(55, 7, 'Wil_Sam7', 15, 'Dia_Pot15', 'pending', '2025-04-22 13:11:47'),
(78, 15, 'Dia_Pot15', 16, 'Kay_Jac16', 'accepted', '2025-04-27 18:34:32'),
(121, 7, 'Wil_Sam7', 1, 'Dav_Rob1', 'accepted', '2025-04-28 21:48:53');

-- --------------------------------------------------------

--
-- Table structure for table `web_users`
--

CREATE TABLE `web_users` (
  `userID` int(11) NOT NULL,
  `username` varchar(10) NOT NULL,
  `fname` varchar(50) DEFAULT NULL,
  `lname` varchar(50) DEFAULT NULL,
  `gender` enum('male','female','other','rather not say') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  `token_expiration` datetime DEFAULT NULL,
  `account_status` enum('pending','active') DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `web_users`
--

INSERT INTO `web_users` (`userID`, `username`, `fname`, `lname`, `gender`, `dob`, `email`, `token`, `token_expiration`, `account_status`, `password`, `phone_number`) VALUES
(1, 'Dav_Rob1', 'Dave', 'Robinson', 'male', '2006-03-12', 'daveRob@gmail.com', 'f34c57e2072afbf409b21c2fb7328a5adb7f2025d8a73035665e41cfeab0a2da', '2025-03-07 07:08:36', 'active', '$2y$10$wgmT0s1sR6LyllZDPD/Siu8ahaYzjCKXDCXUUmm6z1EVNunp8tUJm', '876-xxx-xxxx'),
(2, 'San_Ros2', 'Sandra', 'Rose', 'female', '1989-02-22', 'sanrose@mail.com', '3f1d4d7d234e9f83cf3c1e50717daa9664a01d27112ac6dc3a2565cfc1724f44', '2025-03-10 19:03:44', 'active', '$2y$10$R0m9LdDrYFfcezZe9Os2ZeAX2j8E5s2DQDjfVMFbLC9f3VA1ADE9y', NULL),
(7, 'Wil_Sam7', 'Will', 'Samwells', 'female', '1972-03-12', 'wsamwells@mail.com', 'cd857d45b3003103316f607b8de20c0e3b657febe1811726895ebce7493fa09b', '2025-03-13 07:58:14', 'active', '$2y$10$DvU7iwJAIW2NCzDTcpIxdOr1TmRNdGSxkkOP9oldDLasCZNnfBvXW', '876-xxx-xxxx'),
(8, 'Nat_Dre8', 'Natasha', 'Drews', 'female', '2005-02-18', 'nat_drw@mail.com', '1afe55ff22d624dd72eb5d6665285a3f2f36291524a7df104d5b08056c9e639c', '2025-03-20 12:49:58', 'active', '$2y$10$YWyutWNHEIZ3lFXLXOJSw.w6z8e9nN0Z1r7EEgWkbf4e6KBmLeD5a', '876-xxx-xxxx'),
(10, 'Fre_Lew10', 'Fred', 'Lewis', 'male', '1992-02-27', 'flewis@gmail.com', 'c33fbe0fb13f2b167324e92f0dabf99dd8721af77fd299a3c782364dbe904060', '2025-03-23 13:51:27', 'active', '$2y$10$qrHzUbrkFS9012UMBdPZYe4WnaK9OoMAlPFdiOA1IZOLmjIZpN24m', NULL),
(11, 'Ada_Ros11', 'Adam', 'Rose', 'male', '2000-12-22', 'adamrose@mail.com', 'ff3411d5970b244ac0db44a2d6e6bd67b7f4d16d345e237c51d06d9d1a7ce219', '2025-04-12 11:18:35', 'active', '$2y$10$DYsDH1xqpSK7U421WiDtL.2kUOJK7wr9npXogR7bUBOLC2Ab0EojG', '658-xxx-xxxx'),
(12, 'Gar_Fer12', 'Garville', 'Fergson', 'male', '1999-12-28', 'gferguson@mail.com', '23cc6dab4f5a17c2730c4998e5b084a19af87fcd356cbd15d195feaf887c04cf', '2025-04-22 22:47:47', 'active', '$2y$10$EOJuro6oQPAVIy11roZ41uiODsJ8PBeriuL8mvpTShbTDlhkN91zi', NULL),
(13, 'Rog_bla13', 'Roger', 'blake', 'male', '1987-09-21', 'rogbalke@mail.com', '3509f95ab502fbf1571ab794e200be5f2d10588ab51ce1a18e2078616508eab2', '2025-04-12 12:48:24', 'active', '$2y$10$pNtMAiZMh12uUELkdBAe3.xvp6U0RIW1k2Qtp5RotpqfaL65.zMHm', '8761234567'),
(14, 'Fre_Smi14', 'Fredick', 'Smith', 'male', '1982-12-22', 'fsmith@mail.com', 'bb9546457e8c079f954d94fa54bc86ee81a209359a74b0799f254555d00bc8fd', '2025-04-13 18:20:45', 'active', '$2y$10$BRn0CX2ItGinkoaIAAU7L.i5MqppoWw64mHZNaEo8NLLkNnspvzma', '8761155432'),
(15, 'Dia_Pot15', 'Diana', 'Potter', 'female', '1960-08-03', 'dpotter@mail.com', 'e816f5467081cf9e65e07fd79aa7b55c0d7a3daa3a65f0c7ba2f8c81d6139ef2', '2025-04-13 19:07:03', 'active', '$2y$10$E63apY9OJ521hIf2768wYeV.ch3SDtAm6iQHPbibUV/UDDiPCrcba', '6585678942'),
(16, 'Kay_Jac16', 'Kayla', 'Jackson', 'female', '1983-02-22', 'kayjack@mail.com', 'd4b7dfdcabb209de6ffe9b516077dce7f20c4b4fdc2aaa90159bdf6ae0b3ddb7', '2025-04-18 18:36:02', 'active', '$2y$10$u2NWm1/1ZYFXdki6vXo.JOsU60D4uVgSW5Cljkg.eWOSmFjWW2ILq', '876-xxx-xxxx'),
(17, 'Kem_Chr17', 'Kemar', 'Christie', 'male', '1990-10-22', '1872@mail.com', '7731489034bd3db288d5a20ed72ee0a0592d2c4fe9628bc96014e1c9faf18d5f', '2025-04-26 23:17:11', 'pending', '$2y$10$BITRGdHjxYVOeGTqw2hg8.7mft03BlLC5pOiYF22vOsFYmkdz/oy2', '8761234567');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `communicate`
--
ALTER TABLE `communicate`
  ADD PRIMARY KEY (`communicate_id`),
  ADD KEY `sender_userid` (`sender_userid`,`sender_username`),
  ADD KEY `recipient_userid` (`recipient_userid`,`recipient_username`);

--
-- Indexes for table `family_chat`
--
ALTER TABLE `family_chat`
  ADD PRIMARY KEY (`chat_id`),
  ADD KEY `sender_id` (`sender_id`,`sender_username`),
  ADD KEY `patient_id` (`patient_id`,`patient_username`);

--
-- Indexes for table `family_member`
--
ALTER TABLE `family_member`
  ADD PRIMARY KEY (`userid`,`username`);

--
-- Indexes for table `health_care_prof`
--
ALTER TABLE `health_care_prof`
  ADD PRIMARY KEY (`userid`,`username`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`userid`,`username`);

--
-- Indexes for table `patient_range`
--
ALTER TABLE `patient_range`
  ADD PRIMARY KEY (`patient_userid`,`patient_username`,`hcp_userid`,`hcp_username`),
  ADD KEY `hcp_userid` (`hcp_userid`,`hcp_username`);

--
-- Indexes for table `reading`
--
ALTER TABLE `reading`
  ADD PRIMARY KEY (`userid`,`username`,`readingdate`);

--
-- Indexes for table `request`
--
ALTER TABLE `request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `sender_userid` (`sender_userid`,`sender_username`),
  ADD KEY `recipient_userid` (`recipient_userid`,`recipient_username`);

--
-- Indexes for table `web_users`
--
ALTER TABLE `web_users`
  ADD PRIMARY KEY (`userID`,`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `token` (`token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `communicate`
--
ALTER TABLE `communicate`
  MODIFY `communicate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT for table `family_chat`
--
ALTER TABLE `family_chat`
  MODIFY `chat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `request`
--
ALTER TABLE `request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `web_users`
--
ALTER TABLE `web_users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `communicate`
--
ALTER TABLE `communicate`
  ADD CONSTRAINT `communicate_ibfk_1` FOREIGN KEY (`sender_userid`,`sender_username`) REFERENCES `web_users` (`userID`, `username`),
  ADD CONSTRAINT `communicate_ibfk_2` FOREIGN KEY (`recipient_userid`,`recipient_username`) REFERENCES `web_users` (`userID`, `username`);

--
-- Constraints for table `family_chat`
--
ALTER TABLE `family_chat`
  ADD CONSTRAINT `family_chat_ibfk_1` FOREIGN KEY (`sender_id`,`sender_username`) REFERENCES `web_users` (`userID`, `username`),
  ADD CONSTRAINT `family_chat_ibfk_2` FOREIGN KEY (`patient_id`,`patient_username`) REFERENCES `patient` (`userid`, `username`);

--
-- Constraints for table `family_member`
--
ALTER TABLE `family_member`
  ADD CONSTRAINT `family_member_ibfk_1` FOREIGN KEY (`userid`,`username`) REFERENCES `web_users` (`userID`, `username`);

--
-- Constraints for table `health_care_prof`
--
ALTER TABLE `health_care_prof`
  ADD CONSTRAINT `health_care_prof_ibfk_1` FOREIGN KEY (`userid`,`username`) REFERENCES `web_users` (`userID`, `username`);

--
-- Constraints for table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `patient_ibfk_1` FOREIGN KEY (`userid`,`username`) REFERENCES `web_users` (`userID`, `username`);

--
-- Constraints for table `patient_range`
--
ALTER TABLE `patient_range`
  ADD CONSTRAINT `patient_range_ibfk_1` FOREIGN KEY (`hcp_userid`,`hcp_username`) REFERENCES `health_care_prof` (`userid`, `username`),
  ADD CONSTRAINT `patient_range_ibfk_2` FOREIGN KEY (`patient_userid`,`patient_username`) REFERENCES `patient` (`userid`, `username`);

--
-- Constraints for table `reading`
--
ALTER TABLE `reading`
  ADD CONSTRAINT `reading_ibfk_1` FOREIGN KEY (`userid`,`username`) REFERENCES `patient` (`userid`, `username`);

--
-- Constraints for table `request`
--
ALTER TABLE `request`
  ADD CONSTRAINT `request_ibfk_1` FOREIGN KEY (`sender_userid`,`sender_username`) REFERENCES `web_users` (`userID`, `username`),
  ADD CONSTRAINT `request_ibfk_2` FOREIGN KEY (`recipient_userid`,`recipient_username`) REFERENCES `web_users` (`userID`, `username`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
