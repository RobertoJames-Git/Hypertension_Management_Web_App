-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 22, 2025 at 12:19 AM
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddWebUser` (IN `p_fname` VARCHAR(50), IN `p_lname` VARCHAR(50), IN `p_gender` ENUM('male','female','other','rather not say'), IN `p_dob` DATE, IN `p_email` VARCHAR(200), IN `p_token` VARCHAR(150), IN `p_account_status` ENUM('pending','active'), IN `p_password_hashed` VARCHAR(100), IN `p_userType` ENUM('Hypertensive Individual','Family Member','Healthcare Professional'), IN `p_education_level` VARCHAR(50), IN `p_years_of_exp` ENUM('Less than a year','One to two years','Three to Fours years','Five years or more','Over a decade'), OUT `p_generated_username` VARCHAR(50))   BEGIN
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
        INSERT INTO web_users (fname, lname, gender, dob, email, token, token_expiration,account_status, password)
        VALUES (p_fname, p_lname, p_gender, p_dob, p_email, p_token,p_token_expiration,p_account_status, p_password_hashed);

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

CREATE DEFINER=`root`@`localhost` PROCEDURE `deleteRejectedRequest` (IN `p_request_id` INT)   BEGIN
    DELETE FROM request
    WHERE request_id = p_request_id
      AND request_status = 'rejected';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getAcceptedPatients` (IN `p_loggedInUsername` VARCHAR(50))   BEGIN
    -- Fetch patients where the logged-in user is the sender and the request is accepted
    SELECT 
        r.recipient_username AS patient_username,
        wu.fname,
        wu.lname,
        r.request_date AS connection_date
    FROM request r
    INNER JOIN web_users wu ON r.recipient_username = wu.username
    WHERE r.sender_username = p_loggedInUsername
      AND r.request_status = 'accepted'
      AND EXISTS (
          SELECT 1 FROM patient WHERE username = r.recipient_username
      )

    UNION

    -- Fetch patients where the logged-in user is the recipient and the request is accepted
    SELECT 
        r.sender_username AS patient_username,
        wu.fname,
        wu.lname,
        r.request_date AS connection_date
    FROM request r
    INNER JOIN web_users wu ON r.sender_username = wu.username
    WHERE r.recipient_username = p_loggedInUsername
      AND r.request_status = 'accepted'
      AND EXISTS (
          SELECT 1 FROM patient WHERE username = r.sender_username
      )

    ORDER BY connection_date DESC; -- Sort by most recent connections
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMatchingUsers` (IN `p_username_prefix` VARCHAR(50), IN `p_account_type` VARCHAR(30), IN `p_logged_in_username` VARCHAR(50))   BEGIN
    -- Validate the account type
    IF p_account_type NOT IN ('Family member', 'Health Care Professional', 'Patient') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid account type. Must be "Family member", "Health Care Professional", or "Patient".';
    END IF;

    -- Fetch users based on the account type and username prefix
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
                WHEN EXISTS (
                    SELECT 1 
                    FROM request
                    WHERE request.sender_username = p_logged_in_username
                      AND request.recipient_username = fm.username
                      AND request.request_status = 'rejected'
                ) THEN 'rejected'
                ELSE 'No Request Sent'
            END AS request_status
        FROM family_member fm
        WHERE fm.username LIKE CONCAT(p_username_prefix, '%');

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
                WHEN EXISTS (
                    SELECT 1 
                    FROM request
                    WHERE request.sender_username = p_logged_in_username
                      AND request.recipient_username = hp.username
                      AND request.request_status = 'rejected'
                ) THEN 'rejected'
                ELSE 'No Request Sent'
            END AS request_status
        FROM health_care_prof hp
        WHERE hp.username LIKE CONCAT(p_username_prefix, '%');

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
                WHEN EXISTS (
                    SELECT 1 
                    FROM request
                    WHERE request.sender_username = p_logged_in_username
                      AND request.recipient_username = pt.username
                      AND request.request_status = 'rejected'
                ) THEN 'rejected'
                ELSE 'No Request Sent'
            END AS request_status
        FROM patient pt
        WHERE pt.username LIKE CONCAT(p_username_prefix, '%');
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
        (sender_username = p_user_loggedIn OR recipient_username = p_user_loggedIn)
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
        SET MESSAGE_TEXT = 'Invalid username and/or Password';
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
    DECLARE requestExists INT DEFAULT 0;
    DECLARE p_request_id INT;

    -- Check if a pending request exists between the sender and the logged-in user
    SELECT request_id INTO p_request_id
    FROM request
    WHERE sender_username = p_sender_username
      AND recipient_username = p_user_loggedIn
      AND request_status = 'pending';

    -- Check if a valid request_id was found
    SET requestExists = (p_request_id IS NOT NULL);

    -- Case 1: Request does not exist
    IF requestExists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No matching pending request found.';
    END IF;

    -- Case 2: Update the request status based on the decision
    UPDATE request
    SET request_status = p_decision
    WHERE request_id = p_request_id;

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
(8, 'Nat_Dre8', 'Degree');

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
(7, 'Wil_Sam7', 'Five years or more');

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
(1, 'Dav_Rob1', NULL);

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
(1, 'Dav_Rob1', '2025-03-11', '09:00:00', 120, 100, 83);

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
(27, 8, 'Nat_Dre8', 1, 'Dav_Rob1', 'rejected', '2025-03-20 12:42:03'),
(30, 7, 'Wil_Sam7', 1, 'Dav_Rob1', 'accepted', '2025-03-21 16:36:48');

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
  `password` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `web_users`
--

INSERT INTO `web_users` (`userID`, `username`, `fname`, `lname`, `gender`, `dob`, `email`, `token`, `token_expiration`, `account_status`, `password`) VALUES
(1, 'Dav_Rob1', 'Dave', 'Robinson', 'male', '2006-03-12', 'daveRob@gmail.com', 'f34c57e2072afbf409b21c2fb7328a5adb7f2025d8a73035665e41cfeab0a2da', '2025-03-07 07:08:36', 'active', '$2y$10$wgmT0s1sR6LyllZDPD/Siu8ahaYzjCKXDCXUUmm6z1EVNunp8tUJm'),
(2, 'San_Ros2', 'Sandra', 'Rose', 'female', '1989-02-22', 'sanrose@mail.com', '3f1d4d7d234e9f83cf3c1e50717daa9664a01d27112ac6dc3a2565cfc1724f44', '2025-03-10 19:03:44', 'active', '$2y$10$R0m9LdDrYFfcezZe9Os2ZeAX2j8E5s2DQDjfVMFbLC9f3VA1ADE9y'),
(7, 'Wil_Sam7', 'Will', 'Samwells', 'female', '1972-03-12', 'wsamwells@mail.com', 'cd857d45b3003103316f607b8de20c0e3b657febe1811726895ebce7493fa09b', '2025-03-13 07:58:14', 'active', '$2y$10$DvU7iwJAIW2NCzDTcpIxdOr1TmRNdGSxkkOP9oldDLasCZNnfBvXW'),
(8, 'Nat_Dre8', 'Natasha', 'Drews', 'female', '2005-02-18', 'san_drw@mail.com', '1afe55ff22d624dd72eb5d6665285a3f2f36291524a7df104d5b08056c9e639c', '2025-03-20 12:49:58', 'active', '$2y$10$YWyutWNHEIZ3lFXLXOJSw.w6z8e9nN0Z1r7EEgWkbf4e6KBmLeD5a');

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
  MODIFY `communicate_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `request`
--
ALTER TABLE `request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `web_users`
--
ALTER TABLE `web_users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
