
function displayDatabaseErr(){

    errorContent=document.getElementById("errorContent").getHTML();
    if(errorContent.length >10){
        document.getElementById("errorContent").style="display:block";
        document.getElementById("database_Error").style="display:block";
    }

    
}

// Call the function on page load to display the error if one exits
window.onload = function() {
    displayDatabaseErr();
};