

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Blood Pressure</title>
    <link rel="stylesheet" href="styles/recordBPStyles.css">
    <script src="Javascript/recordBP.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
</head>
<body>

    <?php
        require_once("navbar.php");

        //ensure the user is logged in
        if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
            header("Location:login.php");
            exit();
        }

        require_once("Database/database_actions.php");
    ?>

    <div id="database_Error">
        <span id="errorContent"><?php  echo isset($_SESSION["dbMessage"]) ? htmlspecialchars($_SESSION["dbMessage"]) :'';  unset($_SESSION["dbMessage"]) //ensure result is only shown once?></span>
    </div>

    <div id="support_Network_Alert">

    </div>

    <div id="recordBP_container">
        <h1>Track your blood pressure</h1>

        <p id="patient_range_msg"><?php echo isset($_SESSION["patientRange_Err"])? $_SESSION["patientRange_Err"] : ''; unset($_SESSION["patientRange_Err"]);?>   </p>

        <p>Please enter today's readings</p>
        <form action="Process/validate_recordBP.php" method="post" >


            <label for="">Systolic (mmHg)  <span class="errMessage"><br><?php echo isset($_SESSION["systolicErr"]) ? htmlspecialchars($_SESSION["systolicErr"]) : htmlspecialchars("") ?></span> </label>
            <label for="">Diastolic (mmHg) <span class="errMessage"><br><?php echo isset($_SESSION["diastolicErr"]) ? htmlspecialchars($_SESSION["diastolicErr"]) : htmlspecialchars("") ?></span></label>
            <input class="RecordBP_InputField" type="number" placeholder="Enter value" name="systolic"          value="<?php echo isset($_SESSION["systolic"]) ? htmlspecialchars($_SESSION["systolic"]) : htmlspecialchars("") ?>" required>
            <input class="RecordBP_InputField" type="number" name="diastolic" id="" placeholder="Enter value"   value="<?php echo isset($_SESSION["diastolic"]) ? htmlspecialchars($_SESSION["diastolic"]) : htmlspecialchars("") ?>"  required>

            <label for="">Pulse / Heart Rate (BPM) <span class="errMessage"><br><?php echo isset($_SESSION["heart_rateErr"]) ? htmlspecialchars($_SESSION["heart_rateErr"]) : htmlspecialchars("") ?></span> </label>
            <div></div><!--Just an empty container to make the grid look consistent -->
            <input class="RecordBP_InputField" type="number" name="heart_rate" id="" placeholder="Enter value"  value="<?php echo isset($_SESSION["heart_rate"]) ? htmlspecialchars($_SESSION["heart_rate"]) : htmlspecialchars("") ?>" required>
            <div></div><!--Just an empty container to make the grid look consistent -->
            
            <label for="">Date  <span class="errMessage"><br><?php echo isset($_SESSION["dateErr"]) ? htmlspecialchars($_SESSION["dateErr"]) : htmlspecialchars("") ?></span> </label>
            <label for="">Time  <span class="errMessage"><br><?php echo isset($_SESSION["timeErr"]) ? htmlspecialchars($_SESSION["timeErr"]) : htmlspecialchars("") ?></span> </label>
 
            <input class="RecordBP_InputField" type="date" name="date"  value="<?php echo isset($_SESSION["date"]) ? htmlspecialchars($_SESSION["date"]) : htmlspecialchars("") ?>" required>
            <input class="RecordBP_InputField" type="time" name="time"  value="<?php echo isset($_SESSION["time"]) ? htmlspecialchars($_SESSION["time"]) : htmlspecialchars("") ?>" required>


            <input type="submit"value="Confirm Reading" name="bp_record">
            <input type="reset" value="Cancel">
            
        </form>

        <!--<button id="edit_prev_readings">Edit Previous Readings</button>-->
    </div>


    <div id="Select_Patient_container">
        <p id="select_patient_text"></p>

        <?php  
            // Ensure the user type is either 'Health Care Professional' or 'Family Member'
            if ($_SESSION["userType"] === "Health Care Professional" || $_SESSION["userType"] === "Family Member") {
                // Retrieve the accepted patients from the procedure
                $accepted_Patients = getAcceptedPatients($_SESSION["loggedIn_username"]);
                
                $previously_selected = isset($_POST["selected_patient"]) ? $_POST["selected_patient"] : (isset($_SESSION["selected_patient"]) ? $_SESSION["selected_patient"] : null);

        ?>
            <select name="selected_patient" id="selected_patient_id">
                <?php
                // Check if the array is not empty
                if (!empty($accepted_Patients)) {
                    // Iterate through the array and create dropdown options
                    foreach ($accepted_Patients as $patient) {
                        
                        echo '<option value="' . htmlspecialchars($patient['patient_username']) . '" ' . ($previously_selected === $patient['patient_username'] ? 'selected' : '') . '>' . htmlspecialchars($patient['patient_username']) . '</option>';
                        
                    }
                } else {
                    // Fallback option when no patients are found
                    echo '<option value="" disabled>No patients available</option>';
                }
                ?>

            </select>
                 
        <?php
            }#end of if statement
        ?>

        <?php if ($_SESSION["userType"]==="Health Care Professional" ){ ?>
            <button id="target_range_btn" class="HCP_button" onclick="showBPRangeContainer()"> Modify Patient Target Range</button>
            <button id="view_patient_range" class="HCP_button" onclick="closeContainer()">Display Patient Range</button>
        <?php }//end if  ?>

    </div>

    <?php
        
        // Only include the script if the user is a Family Member or Health Care Professional
        if ($_SESSION["userType"] === "Family Member" || $_SESSION["userType"] === "Health Care Professional") {
    ?>

    <script>

        document.addEventListener("DOMContentLoaded", () => {
            const dropdown = document.getElementById("selected_patient_id");

            // Listen for the 'change' event on the dropdown
            dropdown.addEventListener("change", function () {
                const selectedValue = this.value; // Get the selected value

                // Check if a valid value is selected
                if (selectedValue) {
                    // Create a form dynamically and submit the POST request
                    const form = document.createElement("form");
                    form.method = "POST";
                    form.action = window.location.href; // Send the request to the current page

                    // Add the selected value as a hidden input
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "selected_patient";
                    input.value = selectedValue;

                    // Append the input to the form and submit
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
            
    </script>

    <?php
        }//end of if
    ?>



    <?php


        $page = isset($_GET["page"]) ? intval($_GET["page"]) : 1; // Default page 1 
        $numOfRecordsToDisplay = $_GET["no_of_records_to_display"] ?? 10;//if the get variable is set then I get the value otherwise the value stored is 10

        $patient="";
        //if a patient is logged in then their reading will be shown
        
        if($_SESSION["userType"]=="Patient"){
            $patient=$_SESSION["loggedIn_username"];
        }
        // if the person is logged in as a health care professional or a family member then the webpage will show the first person in the drop down list

        else if ($_SESSION["userType"] === "Health Care Professional" || $_SESSION["userType"] === "Family Member") {

            //check if the user selected a option previously so that the record for that person will be displayed
            if($previously_selected !== null){
                $patient= $previously_selected;
            }
            //if the user did not select any option then we will store the first patient in the  drop down list and display the records for that patient
            else if (empty($patient) && !empty($accepted_Patients)){
                $patient = $accepted_Patients[0]['patient_username'];
            }
            //if the health care prof or family member is not apart of any patients support network
            else if( empty($accepted_Patients[0])){
                $no_patient_message="You are not part of a support network.";
            }

        }
        
        $readings=[];

        //Only search for blood pressure records if the health care prof or family member is in a patient's support network
        if(!empty($patient)){
            $readings = getBloodPressureReadings($patient,$page,$numOfRecordsToDisplay);

            //stores the users selected patient persistenly because the data in the post request is lost on refresh
            $_SESSION["selected_patient"]=$patient;
        }



        // Extract data for Chart.js
        $dates = [];
        $times=[];
        $systolic = [];
        $diastolic = [];
        $heartRate = [];


        /*if records were found and there were no errors then they will be stored in the variables 
        to be displayed in the chart*/

        if (!isset($readings["error"]) && !empty($readings)){

            foreach ($readings as $reading) {
                $dates[] = $reading["readingdate"];
                $times[]=$reading["readingtime"];
                $systolic[] = $reading["systolic"];
                $diastolic[] = $reading["diastolic"];
                $heartRate[] = $reading["heart_rate"];
            }

        }


        $nextPage = $page + 1;
        $prevPage = $page > 1 ? $page - 1 : 1;

        $recordMsg="";
        if(empty($dates) && empty($systolic) && empty($diastolic) && empty($heartRate)){
            $recordMsg="No records Found";
        }
    ?>

    <?php 
    //only display div if user is a health care professional
    if ($_SESSION["userType"]=="Health Care Professional"){ ?>

        <div id="blood_Pressure_Range_container">
            <h2>Modify Patient Range</h2>

            <div id="bp_range_error">
                <?php
                    if(isset($_SESSION["Err_message"])&& strlen($_SESSION["Err_message"])>4){
                        echo $_SESSION["Err_message"];
                        unset($_SESSION["Err_message"]);
                    }
                ?>
            </div>

            <form action="Process/setPatientRange.php" method="post">
                <h4>Set Maxmimum Reading</h4>
                <div>
                    <label for="">Systolic (mmHg)</label>
                    <input type="number" name="max_systolic" id="" value="<?php echo isset($_SESSION["max_systolic"]) ? htmlspecialchars($_SESSION["max_systolic"]) : htmlspecialchars("") ?>">
                </div>

                <div>
                    <label for="">Diastolic (mmHg)</label>
                    <input type="number" name="max_diastolic" id="" value="<?php echo isset($_SESSION["max_diastolic"]) ? htmlspecialchars($_SESSION["max_diastolic"]) : htmlspecialchars("")?>">      
                </div>


                <h4>Set Minimum Reading</h4>
                <div>
                    <label for="">Systolic (mmHg)</label>
                    <input type="number" name="min_systolic" id="" value="<?php echo isset($_SESSION["min_systolic"]) ? htmlspecialchars($_SESSION["min_systolic"]) : htmlspecialchars("")?>">
                </div>

                <div>
                    <label for="">Diastolic (mmHg)</label>
                    <input type="number" name="min_diastolic" id="" value="<?php echo isset($_SESSION["min_diastolic"]) ? htmlspecialchars($_SESSION["min_diastolic"]) : htmlspecialchars("")?>">      
                </div>


                <button type="submit" id="confirm_Range_Btn" value="submit">Confirm Range</button>
            </form>
        </div>

        <?php //unset session variable in form that is used for persistency 
            unset($_SESSION["max_systolic"],$_SESSION["max_diastolic"],$_SESSION["min_systolic"],$_SESSION["min_diastolic"]);
        ?>

    <?php } //endif?>



    <p id="no_support_message"><?php echo isset($no_patient_message) ? htmlspecialchars($no_patient_message) : ""?></p>



    <?php if($_SESSION["userType"]==="Health Care Professional") {?>
    <div id="backgroundOverlay"></div>

    <div id="showPatientRange_container">
        <div id="close_icon_container" onclick="closeContainer()"><img src="images/recordBPImage/closeButton.svg" alt="" width="25px"></div>

        <h2>Patient Range : <?php echo $patient ?></h2>
        <div id="patient_range_details">
            <p>Systolic: x mmHg - x mmHg</p>
            <p>Diastolic: x mmHg - x mmHg</p>
        </div>
    </div>

    <?php }?>


    
    <div id="graph_container">
        <br>

        <div id="heading_and_rec_amt">
            <h1>Blood Pressure Info</h1>


            <div id="modify_records_amt_container">
                <form id="records_form">
                    <label>Number of Records to Display</label>
                    <input type="number" name="no_of_records_to_display" min="5" max="60" placeholder="Default: 10" required>
                    <input type="number" name="page" value="<?php echo htmlspecialchars($page) ?>" hidden>
                    <input type="submit" value="Go">
                </form>

            </div>
        </div>

        <div>

            <div>
                <button class="recordBtns" onclick="fetchReadings(<?php echo htmlspecialchars($prevPage.','.$numOfRecordsToDisplay); ?>)" <?php echo ($page==1) ? "disabled" : "" ?> >Next Readings</button>
                <button class="recordBtns"  onclick="fetchReadings(<?php echo htmlspecialchars($nextPage.','.$numOfRecordsToDisplay); ?>)" <?php echo $recordMsg=="" ?  "" : "disabled"?> >Previous Readings</button>
            </div>



            <p id="records_alert">

            <?php
                    echo isset($readings["error"]) ? htmlspecialchars($readings["error"]) : "" ;
            ?>

            </p>
        </div>

        <canvas id="bpChart"></canvas> <!-- Graph container -->



        <div id="table_container">
        
            <div id="table_data">
                <h3>Date</h3>
                <h3>Time</h3>
                <h3>Systolic(mmHg)</h3>
                <h3>Diastolic(mmHg)</h3>
                <h3>Heart Rate(BPM)</h3>

                <?php  
                    $numOfRecords = sizeof($readings); // Get the number of records

                    if($recordMsg !=="No records Found"){//data is not displayed if no records are available

                        for ($i = 0; $i < $numOfRecords; $i++) { 
                            // Format the date to "Jan 10, 2025"
                            $formattedDate = date("M j, Y", strtotime($dates[$i]));
                            
                            // Format the time to "10:00 AM" or "11:00 PM"
                            $formattedTime = date("g:i A", strtotime($times[$i]));
                ?>
                        <p class="record_details"><?php echo $formattedDate; ?></p>
                        <p class="record_details"><?php echo $formattedTime; ?></p>
                        <p class="record_details"><?php echo $systolic[$i]; ?></p>
                        <p class="record_details"><?php echo $diastolic[$i]; ?></p>
                        <p class="record_details"><?php echo $heartRate[$i]; ?></p>
                <?php 
                        } //end of for loop

                    }//end of if
                ?>

            </div>

        </div>
    </div>

    <script>
        const ctx = document.getElementById('bpChart').getContext('2d');

        // Use the original order of dates and data
        const originalDates = <?php echo json_encode($dates); ?>; // Original dates
        const systolicData = <?php echo json_encode($systolic); ?>; // Original systolic readings
        const diastolicData = <?php echo json_encode($diastolic); ?>; // Original diastolic readings
        const heartRateData = <?php echo json_encode($heartRate); ?>; // Original heart rate readings

        const data = {
            labels: originalDates, // X-axis (Dates)
            datasets: [
                {
                    label: 'Systolic (mmHg)',
                    data: systolicData,
                    borderColor: 'red',
                    backgroundColor: 'rgba(255, 0, 0, 0.2)',
                    fill: false
                },
                {
                    label: 'Diastolic (mmHg)',
                    data: diastolicData,
                    borderColor: 'blue',
                    backgroundColor: 'rgba(0, 0, 255, 0.2)',
                    fill: false
                },
                {
                    label: 'Heart Rate (bpm)',
                    data: heartRateData,
                    borderColor: 'green',
                    backgroundColor: 'rgba(0, 255, 0, 0.2)',
                    fill: false
                }
            ]
        };

        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Measurement'
                        },
                        beginAtZero: false
                    }
                }
            }
        };

        new Chart(ctx, config);

        // Attach a submit event listener to the form
        document.getElementById('records_form').addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent the default form submission behavior

            // Get the input values
            const numOfRecordsToDisplay = document.querySelector('input[name="no_of_records_to_display"]').value; // Number of records
            const page = document.querySelector('input[name="page"]').value; // Page value

            // Call the fetchReadings function with the appropriate parameters
            fetchReadings(page, numOfRecordsToDisplay);
        });
        

    </script>

    <script>
        userType = "<?php echo htmlspecialchars($_SESSION["userType"]) ?>"

        //only display the form to enter blood pressure reading to the patient
        if(userType == "Patient"){
            document.getElementById("recordBP_container").style.display ="block"
        }

        // Get the paragraph element inside the Select_Patient_container div
        const selectPatientText = document.getElementById("select_patient_text");
        const acceptedPatients = <?php echo isset($accepted_Patients) ? json_encode($accepted_Patients) : "[]";?>
        // Get the dropdown element
        const patientDropdown = document.getElementById("selected_patient_id");

        if (userType === "Health Care Professional") {
            selectPatientText.textContent = "Select your Patient";
            
            document.getElementById("Select_Patient_container").style.display = "grid";


            // Get the target range button element
            const targetRangeButton = document.getElementById("target_range_btn");
            const display_Patient_Btn = document.getElementById("view_patient_range");
            // Check if the dropdown has more than 0 options (i.e., it's not empty)
            // Also handles the case where the only option might be the "No patients available" disabled one.
            // We check if there's at least one option that is NOT disabled.
            let hasPatientValidOptions = false;
            for (let i = 0; i < patientDropdown.options.length; i++) {
                if (!patientDropdown.options[i].disabled) {
                    hasPatientValidOptions = true;
                    break;
                }
            }

            // Check if both the dropdown and the button exist in the DOM
            if (patientDropdown && targetRangeButton) {

                if (hasPatientValidOptions) {
                    // If there are valid patient options, show the button
                    targetRangeButton.style.display = "block";
                    display_Patient_Btn.style.display = "block";
                } else {
                    // If the dropdown is empty or only has the disabled placeholder, hide the button
                    targetRangeButton.style.display = "none";
                }


            } else if (targetRangeButton) {
                // If the dropdown doesn't exist for some reason, but the button does, hide the button.
                targetRangeButton.style.display = "none";
            }


            <?php if ($_SESSION["userType"]=="Health Care Professional"){ ?>
                document.addEventListener("DOMContentLoaded", () => {

                    const userRole = "<?php echo $_SESSION['userType']; ?>"; // Fetch user role from PHP session
                    const bpRangeContainer = document.getElementById("blood_Pressure_Range_container");


                    // Check localStorage to restore the last state
                    const isOpen = localStorage.getItem("bpRangeContainerOpen");

                    //If HCP does not have anyone in their support network then they will not see the form to adjust patient range
                    if(!hasPatientValidOptions){
                        return
                    }

                    if (isOpen === "true") {
                        // If the container was previously open
                        bpRangeContainer.style.display = "block";
                        bpRangeContainer.style.opacity = "1";
                        bpRangeContainer.style.height = "auto";
                    } else {
                        // If the container was previously closed
                        bpRangeContainer.style.display = "none";
                        bpRangeContainer.style.opacity = "0";
                        bpRangeContainer.style.height = "0px";
                    }
                });



                function showBPRangeContainer() {
                    const bpRangeContainer = document.getElementById("blood_Pressure_Range_container");

                    if (bpRangeContainer.style.display === "none" || bpRangeContainer.style.opacity === "0") {
                        // Reveal the container and store the state
                        bpRangeContainer.style.display = "block"; // Display immediately for smooth visibility
                        bpRangeContainer.style.height = "auto";
                        setTimeout(() => {
                            bpRangeContainer.style.opacity = "1"; // Start opacity transition
                        }, 10); // Small delay ensures the transition is noticeable
                        bpRangeContainer.style.transition = "opacity 0.5s ease"; // Smooth appearance
                        localStorage.setItem("bpRangeContainerOpen", "true"); // Save state as "open"
                    } else {
                        // Hide the container with a smooth transition
                        bpRangeContainer.style.opacity = "0"; // Trigger fade-out
                        bpRangeContainer.style.transition = "opacity 0.5s ease"; // Smooth disappearance
                        setTimeout(() => {
                            bpRangeContainer.style.display = "none"; // Fully hide after transition completes
                            localStorage.setItem("bpRangeContainerOpen", "false"); // Save state as "closed"
                        }, 500); // Match transition duration
                    }
                }
                                
                function displayErrorForPatientRange() {
                    // Get the element where the message is displayed
                    const errorElement = document.getElementById("bp_range_error");


                        // Get the text content of the element and remove leading/trailing whitespace
                        const message = errorElement.textContent.trim();

                        // Check if there is any message content
                        if (message.length > 0) {
                            // Make the element visible since it has content
                            errorElement.style.display = "block";

                            // Check if the message includes "successful" (case-insensitive)
                            if (message.toLowerCase().includes("success")) {
                                // If it's a success message, set the text color to green
                                errorElement.style.color = "white";
                                errorElement.style.backgroundColor = "green"; // Light green background
                                errorElement.style.borderRadius="5px";
                                errorElement.style.padding="3px";
                            } else {
                                // If it's an error message (doesn't contain "successful"), set the text color to red
                                errorElement.style.color = "red";

                            }
                        }
                    
                }

                // Add the event listener for when the window loads
                window.addEventListener("load", function () {
                    
                    displayErrorForPatientRange();

                });



            <?php }?>



        }
 
        
        else if (userType === "Family Member") {
            selectPatientText.textContent = "Select your Hypertensive Family Member";
            document.getElementById("Select_Patient_container").style.display ="grid"
        }



        document.addEventListener("DOMContentLoaded", () => {
            const noSupportMessage = document.getElementById("no_support_message"); // Get the paragraph element

            // Check if the paragraph has any text
            if (noSupportMessage.textContent.trim() !== "") {
                noSupportMessage.style.display = "block"; // Display the paragraph if it contains text
            }
        });






    </script>




    <br>
</body>
</html>

