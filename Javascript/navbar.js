var navbarPosition="slide-out";
function openSideMenu(){

    // Run the function only when menu is clicked
    checkScreenWidth();
    const navbar = document.getElementById('navbar');
    const currentLeft = navbar.getBoundingClientRect().left; // Get the current position

    // Set the CSS variable for dynamic starting position
    navbar.style.setProperty('--start-position', `${currentLeft}px`);

    if (navbar.classList.contains('slide-in')) {
        // If currently sliding in or fully in, slide out
        navbar.classList.remove('slide-in');
        navbar.classList.add('slide-out');
        navbarPosition="slide-out";

    } else {
        // If currently sliding out or fully out, slide in
        navbar.classList.remove('slide-out');
        navbar.classList.add('slide-in');
        navbarPosition="slide-in";
        //close the drop down if it was previously open when sliding sidebar in
        document.getElementById("dropdownContent").style.display="none";
    }


} 

//this function ensures that that the navbar keeps its layout consistent when
// if the sidebar is opened when the screen is less than 1000px then the screen extended beyond 1000px.
// it does this by changing the navbar between grid and block dynamically based on screen width

function checkScreenWidth() {
    const screenWidth = window.innerWidth; // Get the current width of the window

    //if the sidebar is opened and the screen is extended beyond 1000px this changes the navbar back to grid
    if (screenWidth >= 1000 && document.getElementById('navbar').style.display=='block' ) {
        document.getElementById('navbar').style.display='grid';
    } 
    //if the sidebar was left open and the screen is extended beyond 1000px this changes the navbar back to block 
    else if (screenWidth < 1000 && document.getElementById('navbar').style.display=='grid' ) {
        document.getElementById('navbar').style.display='block';
    } 
}

// Attach the function to the resize event
window.addEventListener('resize', checkScreenWidth);




function removeCreateAccountAndLoginDiv(){
    usernameDiv=document.getElementById("username_span").getHTML();
    
    if(usernameDiv.length>6){
        document.getElementById("user_logged_in").style.display="grid";
      //  document.getElementById("dropdownContent").style.display="block";
        return;
    }
    document.getElementById("register_container").style.display='block';
    document.getElementById("login_container").style.display='block';

    
}


window.onload = function() {
    removeCreateAccountAndLoginDiv();
};

function showDropdown(){

    dropdown = document.getElementById("dropdownContent");
    screenWidth=window.innerWidth;
   // console.log(`Width: ${window.innerWidth}, Height: ${window.innerHeight}`);

    if(navbarPosition=="slide-in"){
        document.getElementById("dropdownContent").style.marginTop="-120px";
        document.getElementById("dropdownContent").style.marginLeft="20px";

    }
    else{
        document.getElementById("dropdownContent").style.marginLeft="0px";
        document.getElementById("dropdownContent").style.marginTop="40px";
    }

    /* We check if the display is set as an empty string because if the 
    element's display was set to none in css by defauly(display:none)then it will return an empty string */
    if(dropdown.style.display =="none" ||dropdown.style.display==""){
        dropdown.style.display="block";
    }
    else{

        dropdown.style.display="none";
    }


    if(screenWidth >= 1071 && navbarPosition=="slide-in"){
        dropdown.style.display="block";
        document.getElementById("dropdownContent").style.marginLeft="0px";
        document.getElementById("dropdownContent").style.marginTop="40px";
        openSideMenu();
 
    }
}