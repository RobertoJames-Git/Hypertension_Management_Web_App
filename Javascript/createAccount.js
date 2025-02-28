function showAttributes(accountType) {

    //make elelments that are apart of a certain class not visible
    document.querySelectorAll(".health_prof_attribute, .family_attribute").forEach(element => {
        element.style.display = "none";
    });



    let className="";


    switch (accountType) {
        case "Family Member":
            className="family_attribute"
            break;
        
        case "Healthcare Professional":
            className="health_prof_attribute"
            break;
        default:
            return; //exit function if the user selected someothing else
            break;
    }

    //show the respective option
    let elements = document.getElementsByClassName(className);
    for (let i = 0; i < elements.length; i++) {
        elements[i].style.display = "grid";
    }
}

function displayDatabaseErr(){

    errorContent=document.getElementById("errorContent").getHTML();
    if(errorContent.length >16){
        document.getElementById("errorContent").style="display:block";
    }

}

// Call the function on page load to display the specialized fields
window.onload = function() {
    let userType = document.getElementById("user_typeID").value;
    showAttributes(userType);

    displayDatabaseErr();
};