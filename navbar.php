
<?php
    session_start();
    // Dynamically determine the base URL
    $base_url = dirname($_SERVER['SCRIPT_NAME']) .'/';
    
    #determine how many levels from the root directiory the file is in by counting
    #the amount of slashes and minus 2 eg: /Major_Project_DHI/. you minus two because by default the project
    # folder will e included and sill have two slashes
    $level_down_count= (substr_count($base_url,'/')-2);
    $level_down='';#used to control how many level back we go
    
    /*this loop is used to determine how many levels back we go to reference the style
    and image file. This makes referencing the files dynamic because the navbar file
    will be referenced in multiple different pages which will be in various locations
    */
    while($level_down_count!=0){
        $level_down_count--;
        $level_down = $level_down .'../';
    }

    
?>

<header>
    <script src="<?php echo htmlspecialchars($level_down)?>Javascript/navbar.js"></script>
    <link rel="stylesheet"  href="<?php echo htmlspecialchars($level_down) ?>styles/indexStyle.css">

    <div id="heading-for-small-screen">
        <div id="website_Name" onclick="window.location.href='<?php echo htmlspecialchars($level_down) ?>index.php'">HypMonitor <img src="<?php echo htmlspecialchars($level_down) ?>images/navbarImages/heart.svg" alt="Image of heart icon" > </div>
        <div id="openNav" onclick="openSideMenu()">Menu &#9776;</div>
    </div>


    <div id="navbar">

        <div id="sidebarclose" onclick="openSideMenu()">X</div>
        <div id="website_Name" onclick="window.location.href='<?php echo htmlspecialchars($level_down) ?>index.php'">HypMonitor <img src="<?php echo htmlspecialchars( $level_down) ?>images/navbarImages/heart.svg" alt="Image of heart icon" > </div>

        <div id="navMenu">
            <div id="home_option" onclick="window.location.href='<?php echo htmlspecialchars($level_down) ?>index.php'"> Home</div>
            <div id="profile_option" onclick="window.location.href='<?php echo htmlspecialchars($level_down) ?>profile.php'" > Profile</div>
            <div id="recordBP_option" onclick="window.location.href='<?php echo htmlspecialchars($level_down) ?>recordBP.php'"> Record BP</div>
            <div onclick="window.location.href='<?php echo htmlspecialchars($level_down) ?>support.php'">Support</div>
            <div onclick="window.location.href='<?php echo htmlspecialchars($level_down) ?>Home/about_us.php'"> About Us</div>
        </div>

        <div id="register_and_login_container">
            <div id="register_container" onclick="window.location.href='<?php echo htmlspecialchars($level_down)?>create_account.php' ">Create an account</div> 
            <div id="login_container" onclick="window.location.href='<?php echo htmlspecialchars($level_down) ?>login.php'" >Login</div>
            <div id="user_logged_in" onclick="showDropdown()"> <img id="profilephoto" src="images/profilePhoto/unisex.png" alt="" width="35px"> <span id="username_span"> <?php echo isset($_SESSION["loggedIn_username"])? htmlspecialchars($_SESSION["loggedIn_username"]."") : htmlspecialchars('') ?></span> <img id="arrowdown" src="images/profilePhoto/image.png" alt="" width="15px"> </div>
           
            <div id="dropdownContent">
                <p onclick="window.location.href='create_account.php'">Create an account</p>
                <p id="dropdown_lgout" onclick="window.location.href='logout.php'">Logout</p>

            </div>

            <div id="create_another_account_div" onclick="window.location.href='create_account.php'">Create another account</div>
        </div>

    </div>
    
</header>



