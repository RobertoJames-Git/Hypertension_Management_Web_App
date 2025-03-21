
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support</title>
    <link rel="stylesheet" href="styles/supportStyle.css">
</head>
<body>
    
    <?php

        //session start already takes place in navbar.php
        require_once('navbar.php');

        if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
            header("Location:logout.php");
            exit();
        }
    ?>

    <div id="Support_Navbar">
        <div id="support_selected">Manage Support Network</div>
        <div>Family Chat</div>
        <div>Healthcare Professional Chat</div>
    </div>


    <div id="modify_support_network_container">

        <div class="support_network_containers" id="support_network_container">
            <h2>Modify your support network</h2>

            <div id="prof_in_sup_network">
                <h3>Healthcare Professional</h3>
                
                <div class="support_net_details">   
                    <span class="username">Username</span> 
                    <div> <span>Jan 1, 2025</span> </div> 
                </div>

                <button>Add a Health Care Professional</button>
            </div>


            <div id="family_mem_in_sup_network">
                <h3>Family Members</h3>
                <div class="support_net_details">   
                    <span class="username">Username</span> 
                    <div> <span>Jan 1, 2025</span> </div> 
                </div>
                <button>Add a Family Member</button>
            </div>

            <br>
            <button>Remove from Support Network</button>
            
        </div>



        <div class="support_network_containers" id="add_supp_net_container">
            <h2>Add to your Support Network</h2>

            <label>Searching for a </label>
            <select name="type_of_user" id="type_of_user_ID">
                <!-- Options will be dynamically populated based on user type -->
            </select>
            <input type="text" id="userSearchID" name="search_for_prof_and_fam_member" placeholder="Enter username here">

            <div id="search_results_container"></div>
        </div>


        <div class="support_network_containers" id="pending_container">
            <h2>Pending Requests</h2>

            <?php

                require_once("Database/database_actions.php");
                
                // Retrieve pending requests for the logged-in user
                $pendingData = getPendingRequests($_SESSION["loggedIn_username"]);

            ?>

            <?php if (isset($pendingData['error'])): ?>

                <div class="error_message">
                    <p>Error: <?php echo htmlspecialchars($pendingData['error']); ?></p>
                </div>

            <?php elseif (!empty($pendingData)): ?>
                <!-- Display each pending request -->
                <?php foreach ($pendingData as $request): ?>
                    <div class="support_net_details">
                        <span class="username"><?php echo htmlspecialchars($request['from_username']); ?></span>
                        
                        <div class="support_net_details_btn">
                            <button class="accept_button" data-username="<?php echo htmlspecialchars($request['from_username']); ?>">Accept</button>
                            <button class="reject_button" data-username="<?php echo htmlspecialchars($request['from_username']); ?>">Reject</button>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Display if there are no pending requests -->
                <div class="no_requests_message">
                    <p>No pending requests.</p>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="support_network_containers" id="rejected_container">
        <h2>Rejected Request</h2>
            <?php
            $rejectedData = getRejectedRequests($_SESSION["loggedIn_username"]);
            ?>

            <?php if (isset($rejectedData['error'])): ?>

                <div class="error_message">
                    <p>Error: <?php echo htmlspecialchars($rejectedData['error']); ?></p>
                </div>

            <?php elseif (!empty($rejectedData)): ?>
                <?php foreach ($rejectedData as $request): ?>
                    <div class="support_net_details">
                        <span class="username"><?php echo htmlspecialchars($request['Sender']); ?></span>
                        <button class="remove_button" data-request-id="<?php echo htmlspecialchars($request['request_id']); ?>">Remove</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no_requests_message">
                    <p>No rejected requests.</p>
                </div>
            <?php endif; ?>
        </div>

        


    
    </div>

    <br>


    
    <script>
        // Retrieve user type from the session (injected by PHP)
        const userType = <?php echo json_encode($_SESSION["userType"]); ?>;

        // Select the dropdown element
        const userTypeOptions = document.getElementById("type_of_user_ID");

        // Clear and populate the dropdown based on the user type
        if (userType === "Patient") {
            // Patient can see Family Member and Health Care Professional
            userTypeOptions.innerHTML = `
                <option value="Family member">Family Member</option>
                <option value="Health Care Professional">Health Care Professional</option>
            `;
        } else if (userType === "Family Member") {
            // Family Member can see Patient
            userTypeOptions.innerHTML = `
                <option value="Patient">Patient</option>
            `;
        } else if (userType === "Health Care Professional") {
            // Health Care Professional can see Patient
            userTypeOptions.innerHTML = `
                <option value="Patient">Patient</option>
            `;
        } else {
            // If the user type is not recognized, clear the dropdown (or handle appropriately)
            userTypeOptions.innerHTML = `<option disabled>No options available</option>`;
        }
    </script>



    <script>


        // Attach event listeners
        const userTypeDropdown = document.getElementById('type_of_user_ID');
        const searchBox = document.getElementById('userSearchID');
        const resultsContainer = document.getElementById('search_results_container');

        // Function to fetch and display results
        async function fetchSearchResults() {
            
            const userType = userTypeDropdown.value;
            const searchText = searchBox.value;

            // Only perform a search if there is input in the search box
            if (searchText.trim() === "") {
                resultsContainer.innerHTML = ""; // Clear results if no input
                return;
            }

            try {
                // Send a request to the backend
                const response = await fetch(`Process/support_network_search.php?type=${encodeURIComponent(userType)}&username=${encodeURIComponent(searchText)}`);
                const data = await response.json();

                // Clear the results container
                resultsContainer.innerHTML = "";

                // Display results
                if (data.length === 0) {
                    resultsContainer.innerHTML = "<div class='search_result'>No results found</div>";
                } else {
                    data.forEach(user => {
                        // Create a container div for the username and button
                        const resultDiv = document.createElement('div');
                        resultDiv.classList.add('search_result');

                        // Create a span to display the username
                        const usernameSpan = document.createElement('span');
                        usernameSpan.textContent = user.username;

                        // Create a button
                        const sendRequestButton = document.createElement('button');
                        sendRequestButton.textContent = "Send Request";
                        sendRequestButton.classList.add('send_request_button');

                        // Capture the username in a local variable for click events
                        const username = user.username;

                        // Add a click event to the button
                        sendRequestButton.addEventListener('click', () => {

                            if (sendRequestButton.textContent === "Request Pending") {
                                
                                // Show a confirmation alert for canceling the request
                                const confirmCancel = confirm("Are you sure you want to cancel your request?");
                                
                                if (!confirmCancel) {
                                    return; // Exit if the user cancels the confirmation
                                }
                            }

                            fetch(`Process/support_network_send_request.php?username=${encodeURIComponent(username)}&type=${encodeURIComponent(userType)}`, { method: 'GET' })
                                .then(response => response.json())
                                .then(result => {
                                    
                                    if (result.success) {
                                        // Handle button state dynamically
                                        if (sendRequestButton.textContent === "Send Request") {
                                            // Change to Request Pending
                                            sendRequestButton.textContent = "Request Pending";
                                            sendRequestButton.style.backgroundColor = "#153bb0";
                                        } else if (sendRequestButton.textContent === "Request Pending") {
                                            // Change back to Send Request after canceling
                                            sendRequestButton.textContent = "Send Request";

                                            sendRequestButton.style.backgroundColor = ""; // Reset to default
                                        }
                                    } else {
                                        alert(`Failed to process request: ${result.message}`);
                                    }
                                })
                                .catch(error => {
                                    console.error("Error processing request:", error);
                                    alert("An error occurred while processing the request.");
                                });
                        });

                        // Update the button text and behavior based on request_status
                        if (user.request_status === "pending") {
                            sendRequestButton.textContent = "Request Pending";
                            sendRequestButton.style.backgroundColor = "#153bb0";
                        } else if (user.request_status === "accepted") {
                            sendRequestButton.textContent = "Request Accepted";
                            sendRequestButton.addEventListener('click', () => {
                                window.location.href = `Process/manage_request.php?username=${encodeURIComponent(username)}`;
                            });
                        }

                        console.log(user);
                        // Append the username and button to the result div
                        resultDiv.appendChild(usernameSpan);
                        resultDiv.appendChild(sendRequestButton);

                        // Append the result div to the results container
                        resultsContainer.appendChild(resultDiv);
                    });
                }
            } catch (error) {
                console.error("Error fetching search results:", error);
            }
        }

        // Trigger search when typing or when the user type changes
        searchBox.addEventListener('input', fetchSearchResults);
        userTypeDropdown.addEventListener('change', fetchSearchResults);
    </script>


    <script>

        // Attach event listeners to accept and reject buttons
        document.addEventListener("DOMContentLoaded", () => {
            const pendingContainer = document.getElementById("pending_container");

            pendingContainer.addEventListener("click", async (event) => {
                const target = event.target;

                // Check if the clicked element is an "Accept" or "Reject" button
                if (target.classList.contains("accept_button") || target.classList.contains("reject_button")) {
                    const senderUsername = target.getAttribute("data-username");
                    const decision = target.classList.contains("accept_button") ? "accepted" : "rejected";

                    try {
                        // Send GET request to Process/managePendingRequest.php
                        const response = await fetch(`Process/managePendingRequest.php?sender=${encodeURIComponent(senderUsername)}&decision=${encodeURIComponent(decision)}`, { method: 'GET' });
                        const result = await response.json();

                        // Check if the request was processed successfully
                        if (result.success) {
                            // Remove the corresponding record from the DOM
                            const parentRecord = target.closest(".support_net_details");
                            if (parentRecord) {
                                parentRecord.remove();
                            }
                            alert(`Request from ${senderUsername} has been ${decision}.`);
                        } else {
                            alert(`Failed to process the request: ${result.message}`);
                        }
                    } catch (error) {
                        console.error("Error processing request:", error);
                        alert("An error occurred while processing the request.");
                    }
                }
            });
        });


    </script>



    <script>

        document.addEventListener("DOMContentLoaded", () => {
            const rejectedContainer = document.getElementById("rejected_container");

            rejectedContainer.addEventListener("click", async (event) => {
                const target = event.target;

                // Check if the clicked element is a "Remove" button
                if (target.classList.contains("remove_button")) {
                    const requestId = target.getAttribute("data-request-id");

                    try {
                        // Send a GET request to Process/manageRejectRequest.php
                        const response = await fetch(`Process/manageRejectRequest.php?requestId=${encodeURIComponent(requestId)}`, { method: 'GET' });
                        const result = await response.json();

                        // If the request was successfully processed
                        if (result.success) {
                            // Remove the corresponding element from the DOM
                            const parentRecord = target.closest(".support_net_details");
                            if (parentRecord) {
                                parentRecord.remove();
                            }
                            alert("Rejected request removed successfully.");
                        } else {
                            alert(`Failed to remove the request: ${result.message}`);
                        }
                    } catch (error) {
                        console.error("Error removing request:", error);
                        alert("An error occurred while removing the request.");
                    }
                }
            });
        });

    </script>

</body>
</html>