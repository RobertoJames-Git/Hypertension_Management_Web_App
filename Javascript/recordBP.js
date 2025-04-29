



function displayDatabaseErr() {
    const errorContent = document.getElementById("errorContent").innerHTML; // Get the content
    const databaseErrorElement = document.getElementById("database_Error"); // Reference the parent element

    if (errorContent.length > 0) { // Check if the content has length
        databaseErrorElement.style.display = "block"; // Show the error block
        document.getElementById("errorContent").style.display = "block"; // Show the inner content

        // Check if "Successfully" (case-insensitive) is in the content
        if (errorContent.toLowerCase().includes("successfully")) {
            databaseErrorElement.style.backgroundColor = "green"; // Set background color to green
        } else {
            databaseErrorElement.style.backgroundColor = "red"; // Set background color to red
        }
    }
}

function displayRecordsAlert() {
    const alertMsg = document.getElementById("records_alert");

    // Trim any whitespace in case textContent has spaces
    if (alertMsg.textContent.trim().length > 0) {
        alertMsg.style.display = "block"; // Make it visible if content exists
    } else {
        alertMsg.style.display = "none"; // Ensure it remains hidden otherwise
    }
}


function styleDisabledButtons() {
    // Get all buttons with the class 'recordBtns'
    const buttons = document.querySelectorAll('.recordBtns');
    
    // Iterate over each button
    buttons.forEach(button => {
        // Check if the button is disabled
        if (button.disabled) {
            // Set the cursor to default
            button.style.cursor = 'default';
            
            // Change the background color to a less intense blue
            button.style.backgroundColor = '#6c8efb'; // Lighter shade of blue
        }
    });
}

function checkRecords() {
    // Select all <p> elements with the class "record_details"
    const recordElements = document.querySelectorAll('.record_details');

    // Get the element with id "table_data"
    const tableData = document.getElementById('table_data');

    // If no <p> elements with class "record_details" exist, hide "table_data"
    if (recordElements.length === 0) {
        tableData.style.display = 'none';
    }
}








function fetchReadings(page,numOfRecordsToDisplay) {
    window.location.href = "recordBP.php?page=" + page+ "&no_of_records_to_display="+ numOfRecordsToDisplay; // Reload with new page number
}

function displayPatientRangeMsg() {
    const alertMsg = document.getElementById("patient_range_msg");
    const message = alertMsg.textContent.trim();
    // Trim any whitespace in case textContent has spaces
    if (message.length > 0) {
        alertMsg.style.display = "block"; // Make it visible if content exists
    }


    // Check if the message is not empty and includes "within range" (case-insensitive)
    if (message.length > 0 && message.toLowerCase().includes("within range")) {

        alertMsg.style.backgroundColor = "green";
        alertMsg.style.color = "white";
    }

    
}




// Add the event listener for when the window loads
window.addEventListener("load", function () {
    
    styleDisabledButtons();
    displayDatabaseErr();
    displayRecordsAlert()
    checkRecords();
    displayPatientRangeMsg();
});




