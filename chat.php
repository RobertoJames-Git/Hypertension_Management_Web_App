
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support</title>
    <link rel="stylesheet" href="styles/supportStyle.css">
    <link rel="stylesheet" href="styles/chatStyle.css">
    <script src="Javascript/supportNetwork.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
    
    <?php

        //session start already takes place in navbar.php
        require_once('navbar.php');
        require_once("Database/database_actions.php");

        if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
            header("Location:logout.php");
            exit();
        }
        
        $acceptedUserType=["Health Care Professional","Family Member","Patient"];
        
        if(!isset($_GET["chatWith"]) || !in_array($_GET["chatWith"],$acceptedUserType) ){
            header("Location:support_network.php");
            exit();
        }

        require_once('support_net_navbar.php');
    ?>

    <?php

        function extractIDFromUsername($username){

            preg_match('/\d+/', $username, $matches);
            $userID = isset($matches[0]) ? intval($matches[0]) : null;
            
            return $userID;
        }
        
        $chatWithUserID = "";
        $chatWithUserUsername = "";

        // Check if the logged-in user is chatting with a healthcare professional and is a patient
        if ($_GET['chatWith'] == "Health Care Professional" && $_SESSION["userType"] == "Patient") {
            $healthProfData = getAcceptedHealthProfessional($_SESSION["loggedIn_username"]);
            $chatWithUserID = isset($healthProfData[0]["userID"]) ? $healthProfData[0]["userID"] : "";
            $chatWithUserUsername = isset($healthProfData[0]["username"]) ? $healthProfData[0]["username"] : "";
            $headingData="Health Professional Chat : ".$healthProfData[0]["fname"]. " ". $healthProfData[0]["lname"] ;
        }

        else if($_GET['chatWith'] == "Patient" && $_SESSION["userType"] == "Health Care Professional"){
            
            $patientData= getPatientsForSupportUser($_SESSION["loggedIn_username"]);
            $chatWithUserID = extractIDFromUsername($_GET["chat_with_patient"]);
            $chatWithUserUsername = $_GET["chat_with_patient"];
            $headingData="Patient Chat";
        }

        // Extract the numeric part from the logged-in username
        $extractedID = extractIDFromUsername($_SESSION["loggedIn_username"]);
        

    ?>

    <div id="chat_header_and_Select">
        <h3 id="chatHeader"><?php echo htmlspecialchars($headingData); ?></h3>
        
        <select name="chat_to_patient" id="chat_to_patientID">
            <option value="">Select Patient</option>
            <?php
            if (isset($patientData) && is_array($patientData)) {
                foreach ($patientData as $patient) {
                    
                    echo '<option value="' . htmlspecialchars($patient['patient_username']) . '" ' . 
                        ((isset($_GET["chat_with_patient"]) && $_GET["chat_with_patient"] == $patient['patient_username']) ? "selected" : "") . '>' . 
                        htmlspecialchars($patient['patient_username']) . 
                        '</option>';
                    
                }
            } else {
                echo '<option value="" disabled>No patients found</option>';
            }
            ?>
        </select>

    </div>

    <div id="chat_and_input_container">
        <div id="chat_container">
            <!-- Messages will be dynamically appended here -->
        </div>

        <div id="chat_input_container">
            <textarea id="chat_textarea" placeholder="Type your message..." rows="2"></textarea>
            <button id="send_button">Send</button>
        </div>
    </div>
    




    <script>

        
        $(document).ready(function() {
            // Function to load chat messages
            function loadChat() {
                $.ajax({
                    url: 'Process/get_messages.php',
                    type: 'GET',
                    data: { 
                        senderId: <?php echo $extractedID; ?>,
                        recipientId: <?php echo json_encode($chatWithUserID); ?>
                    },
                    success: function(data) {
                        $('#chat_container').html(data);
                    }
                });
            }

            // Load chat messages initially
            loadChat();

            // Send message on button click
            $('#send_button').click(function() {

                var message = $('#chat_textarea').val();
                if(message.trim() != '') {
                    $.ajax({
                        url: 'Process/send_message.php',
                        type: 'POST',
                        data: {
                            senderId: <?php echo $extractedID;?>,
                            recipientId: <?php echo $chatWithUserID; ?>,
                            message: message 
                            },
                        success: function() {
                            $('#chat_textarea').val('');

                        },
                        error: function(xhr, status, error) {
                            // This function will be called if there's an error
                            console.error("Error sending message:", status, error);
                            console.error("Response Text:", xhr.responseText);
                            // Optionally, display an error message to the user
                            $('#chat_container').append("<div class='error-message'>Failed to send message. Please try again.</div>");
                        }
                    });
                }
            });

            // Periodically update the chat (using setInterval for simplicity, consider WebSockets for production)
            setInterval(loadChat, 2000); // Reload chat every 2 seconds
        });
            


        document.addEventListener('DOMContentLoaded', function() {
            const selectElement = document.getElementById('chat_to_patientID');

            selectElement.addEventListener('change', function() {
                const selectedValue = this.value; // Get the selected value

                // Get the current URL
                const currentUrl = new URL(window.location.href);

                // Add or update the selected value as a query parameter
                currentUrl.searchParams.set('chat_with_patient', selectedValue);

                // Refresh the page and navigate to the updated URL
                window.location.href = currentUrl.toString();
                
            });
        });


        userType= "<?php echo htmlspecialchars($_SESSION["userType"]);?>";
        
        if(userType =="Family Member"|| userType =="Health Care Professional"){
            const patientDropDown = document.getElementById('chat_to_patientID');
            patientDropDown.style.display="block";

        }
        else if(userType =="Patient"){
            
            document.getElementById('chat_header_and_Select').style.columnGap = '0px';
        }

    </script>
            

</body>


</html>