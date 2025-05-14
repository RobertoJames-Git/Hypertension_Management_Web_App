
function displayDatabaseErr(){

    errorContent=document.getElementById("errorContent").getHTML();
    if(errorContent.length >10){
        document.getElementById("errorContent").style="display:block";
        document.getElementById("database_Error").style="display:block";
    }

    
}



function startCountdown() {
    let countdownElement = document.getElementById("attempt_countdown");

    // Check if the countdown element exists
    if (!countdownElement) {
        return;
    }

    let remainingMillis = parseInt(countdownElement.innerText, 10);

    if (isNaN(remainingMillis) || remainingMillis <= 0) {
        countdownElement.innerText = "0:00";
        return;
    }

    function updateCountdown() {
        if (remainingMillis <= 0) {
            countdownElement.innerText = "0:00";
            return;
        }


        let minutes = Math.floor(remainingMillis / 60000);
        let seconds = Math.floor((remainingMillis % 60000) / 1000);

        countdownElement.innerText = `${minutes}:${seconds < 10 ? "0" : ""}${seconds}`;

        if (remainingMillis > 0) {
            remainingMillis -= 1000;
            setTimeout(updateCountdown, 1000);
        }
    }

    updateCountdown();
}



// Call the function on page load to display the error if one exits
window.onload = function() {
    displayDatabaseErr();
    startCountdown();
};