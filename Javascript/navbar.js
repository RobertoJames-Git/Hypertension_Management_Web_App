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
    } else {
        // If currently sliding out or fully out, slide in
        navbar.classList.remove('slide-out');
        navbar.classList.add('slide-in');
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


