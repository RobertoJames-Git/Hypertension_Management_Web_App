
<?php


    session_start();

    //ensure the user is logged in
    if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
        header("Location:../logout.php");
        exit();
    }

    require_once('../Database/database_actions.php');

    if (isset($_GET['requestId'])) {
        $requestId = intval($_GET['requestId']);

        try {
            // Call the PHP function that interacts with the stored procedure to delete the rejected request
            $result = deleteRejectedRequest($requestId);

            // Send success response
            if ($result['success']) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => $result['error']]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request parameters.']);
    }
