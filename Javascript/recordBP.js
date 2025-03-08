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


// Add the event listener for when the window loads
window.addEventListener("load", function () {
    displayDatabaseErr();
    displayRecordsAlert()
});




