function openSideMenu(){

    // Run the function only when menu is clicked
    checkScreenWidth();
    navigationBar = document.getElementById('navbar');


    if (navigationBar.style.display==''){
        navigationBar.style.display='block';
        navigationBar.style.animation='slideIn 0.5s ease-in forwards';
        return;
    }

    if(navigationBar.style.display!='grid'){
        navigationBar.style.animation='slideOut 0.5s ease-out backwards';
    }
    
    setTimeout(() => {
        if (navigationBar.style.animation=='slideOut 0.5s ease-out backwards'){
            navigationBar.style.display='';
            navigationBar.style.animation='slideIn 0.5s ease-in forwards';
        }
    }, 490);

    
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


