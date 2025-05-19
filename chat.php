
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

        //Validate the chatWith parameter to ensure it is one of the accepted user types
        if(!isset($_GET["chatWith"]) || !in_array($_GET["chatWith"],$acceptedUserType) ){
            header("Location:support_network.php");
            exit();
        }

        require_once('support_net_navbar.php');
    ?>

    <?php

        function extractIDAndUSername($username) {
            preg_match('/(\D+)(\d+)/', $username, $matches);

            $userID = isset($matches[2]) ? intval($matches[2]) : null;

            return $userID;
        }

        $chatWithUserID = "";
        $chatWithUserUsername = "";

        // Check if the logged-in user is chatting with a healthcare professional and is a patient
        if ($_GET['chatWith'] == "Health Care Professional" && $_SESSION["userType"] == "Patient") {
            $healthProfData = getAcceptedHealthProfessional($_SESSION["loggedIn_username"]);

            if(count($healthProfData)>0){

                $chatWithUserID = isset($healthProfData[0]["userID"]) ? $healthProfData[0]["userID"] : "";
                $chatWithUserUsername = isset($healthProfData[0]["username"]) ? $healthProfData[0]["username"] : "";
                $headingData="Health Professional Chat : ".$healthProfData[0]["fname"]. " ". $healthProfData[0]["lname"] ;
            }
            else{

                echo'<h3 class="No_Members">Add a Health Care Professional to your Support Network</h3>';
                exit();
            }
        }
        //if a Health care professional or Family Member is chatting with a Patient
        else if($_GET['chatWith'] == "Patient" &&($_SESSION["userType"] == "Health Care Professional" ||$_SESSION["userType"] == "Family Member") ){
            
            /* Add a check here th */

            $patientData= getPatientsForSupportUser($_SESSION["loggedIn_username"]);

            //checks if the health care professional or family member does not have anyone in their support network to chat with
            if(!isset($patientData)|| count($patientData)==0){

                echo'<h3 class="No_Members">Add a Patient to your Support Network</h3>';
                exit();
            }
            
            if(isset($_GET["chat_with_patient"]) && $_GET["chat_with_patient"]!= ""){
                $chatWithUserID = extractIDAndUSername($_GET["chat_with_patient"]);
                $chatWithUserUsername = $_GET["chat_with_patient"];
            }

            $headingData= $_SESSION["userType"]=="Health Care Professional"?"Patient Chat":"Family Member Chat";
        

        }

        //if a patient want to chat with a family member

        else if($_GET['chatWith'] == "Family Member" && $_SESSION["userType"] == "Patient" ){
            
            //if the patient is the one that is sending a message then extract their userID and username from session
            $chatWithUserID = extractIDAndUSername($_SESSION["loggedIn_username"]);
            $chatWithUserUsername = $_SESSION["loggedIn_username"];
            $headingData="Family Chat";
       
            $supportNetwork = getSupportNetwork($_SESSION["loggedIn_username"]);
            
            $foundFamMember=false;
            foreach ($supportNetwork as $value){
                if ($value['role'] === "Family Member"){
                    $foundFamMember = true;
                    break;
                }
                
            }

            if($foundFamMember ===false){

                echo'<h3 class="No_Members">Add a Family Member to your Support Network</h3>';
                exit();
            }




        }
        
    

        //Invalid user interactions eg(Patient --> Patient or Family Member --> Family Member or Health Care Professional --> Health Care Professional or Family Member --> Health Care Professional)
        else {
            
            echo "<br><h1 style='color:red;margin:auto;width:max-content;'>Invalid User Interaction</h1>";

            exit();
    
        }
 
         
        // Extract the numeric part from the logged-in username
        $extractedID = extractIDAndUSername($_SESSION["loggedIn_username"]);
        
    ?>


    <div id="chat_header_and_Select">
        <h3 id="chatHeader"><?php echo htmlspecialchars($headingData); ?></h3>
        
        <select name="chat_to_patient" id="chat_to_patientID">
            
            

            <?php

                $dropDownMessage="";
                //personalize message in drop down depending on usertype
                if ($_SESSION["userType"]=="Health Care Professional"){
                    $dropDownMessage="Patient";
                }
                else if ($_SESSION["userType"]=="Family Member"){
                    $dropDownMessage="Family Member";
                }
                
                if (isset($patientData) && count($patientData) > 0) {

                    echo'<option value="">Select a '.$dropDownMessage.'</option>';

                    foreach ($patientData as $patient) {
                        
                        echo '<option value="' . htmlspecialchars($patient['patient_username']) . '" ' . 
                            ((isset($_GET["chat_with_patient"]) && $_GET["chat_with_patient"] == $patient['patient_username']) ? "selected" : "") . '>' . 
                            htmlspecialchars($patient['patient_username']) . 
                            '</option>';
                        
                    }
                } else {

                        echo '<option value="" disabled>No '.$dropDownMessage.' found</option>';
                }
                
            ?>
        </select>

    </div>

    <?php

        #check if logged in user is a HCP or Family member and if they have selected a patient to chat with
        if(($_SESSION["userType"] == "Health Care Professional" || $_SESSION["userType"] == "Family Member") && isset($_GET["chat_with_patient"], $patientData) ){

            $patientFromUrl = $_GET["chat_with_patient"];
            $isValidPatientSelected = false; // Flag to track if the selected patient is valid

            // Check if $patientData is not empty and is an array
            if (!empty($patientData) && is_array($patientData)) {
                // Iterate through the patient data array
                foreach ($patientData as $patient) {
                    // Check if the 'patient_username' key exists and matches the selected patient
                    if (isset($patient['patient_username']) && $patient['patient_username'] === $patientFromUrl) {
                        $isValidPatientSelected = true; // Found a match
                        break; // Exit the loop once a match is found
                    }
                }
            }


            if (!$isValidPatientSelected) {

                if(isset($_GET["chat_with_patient"]) && !empty($_GET["chat_with_patient"])){
                
                    echo"<h3 class='No_Members'>You are not apart of $patientFromUrl's support network<br>Please select someone from the drop down.</h3>";
                }
                else{
                    echo"<h3 class='No_Members'>Please select someone form the dropdown.</h3>";
                }
            }

        }

    ?>

        
    
    <?php 

        //checks if the patient username that was retrieved from the url is in the support network of the HCP and Family Member
        //Patient does not rely on $_GET request since they do not select someone form the drop down
        if( ( ($_SESSION["userType"] == "Health Care Professional" || $_SESSION["userType"] == "Family Member") && (isset($isValidPatientSelected) && $isValidPatientSelected)) || $_SESSION["userType"] == "Patient"  ){

    ?>
        <div id="chat_and_input_container">
            <div id="chat_container">
                <!-- Messages will be dynamically appended here -->
            </div>

            <div id="chat_input_container">
                <textarea id="chat_textarea" placeholder="Type your message..." rows="2"></textarea>
                <button id="send_button">Send</button>
            </div>
        </div>

    <?php  }//endif
            else if  (($_SESSION["userType"] == "Health Care Professional" || $_SESSION["userType"] == "Family Member")&& (!isset($_GET["chat_with_patient"]))){
                echo'<h3 class="No_Members">Please select someone from the drop down.</h3>';
            }
    ?>

    
    <?php 
    
    //if the patient username in the url is not in your support network then dont request any info from the backend
    if((isset($isValidPatientSelected) && $isValidPatientSelected)||!isset($isValidPatientSelected)) {?>

    <script>

        userType = "<?php echo htmlspecialchars($_SESSION['userType']); ?>";
        userOption = "<?php echo htmlspecialchars($_GET['chatWith']); ?>";
        
            
        //Only display drop down to select what patient you want to talk to if the logged in user is a Family Member or Health Care Professional
        if(userType ==="Family Member"|| userType ==="Health Care Professional"){

            const patientDropDown = document.getElementById('chat_to_patientID');
            patientDropDown.style.display="block";
            document.getElementById('chat_header_and_Select').style.columnGap = '30px';

        }

        $(document).ready(function() {
            // Function to load chat messages
            function loadChat() {
                $.ajax({
                    url: 'Process/get_messages.php',
                    type: 'GET',
                    data: {
                        senderId: <?php echo $extractedID; ?>,
                        <?php echo (isset($chatWithUserUsername) && $chatWithUserUsername != "") ? "patientUsername: '" . htmlentities($chatWithUserUsername) . "'," : ""; ?>
                        recipientId: <?php echo json_encode($chatWithUserID); ?>,
                        chatWith: userOption
                    },
                    success: function(data) {
                        $('#chat_container').html(data);
                    }
                });
            }

            // Load chat messages initially

            <?php 
            
            // Periodically update the chat (using setInterval for simplicity)

            if((isset($_GET["chat_with_patient"]) && $_GET["chat_with_patient"]!="" && ($_SESSION['userType']=="Family Member"||$_SESSION['userType']=="Health Care Professional"))||($_SESSION['userType']=="Patient")){
                    echo "loadChat();\n\t\t\tsetInterval(loadChat, 2000);";
                
            }

            ?>
            
            $('#send_button').click(function () {
    var message = $('#chat_textarea').val();
    if (message.trim() !== '') {
        $.ajax({
            url: 'Process/send_message.php',
            type: 'POST',
            data: {
                senderId: <?php echo $extractedID; ?>,
                <?php echo (isset($chatWithUserID) && $chatWithUserID != "") ? "recipientId: '" . htmlentities($chatWithUserID) . "'," : ""; ?>
                <?php echo (isset($chatWithUserUsername) && $chatWithUserUsername != "") ? "patientUsername: '" . htmlentities($chatWithUserUsername) . "'," : ""; ?>
                message: message,
                chatWith: userOption
            },
            success: function (response) {
                // Clear the chat message input field on success
                $('#chat_textarea').val('');
            },
            error: function (xhr, status, error) {
                console.error("Error sending message:", status, error);

                // Parse JSON response to display the error message
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.status === "error") {
                        alert(response.message); // Display the error message in an alert
                    } else {
                        alert("An unexpected error occurred.");
                    }
                } catch (e) {
                    console.error("Failed to parse JSON response:", e);
                    alert("An unexpected error occurred while sending the message.");
                }
            }
        });
    }
    });  

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
        


    </script>

    <?php }?>


</body>


</html>