
<?php
    session_start(); //start session to display username
    
?>

<header>
    <script src="Javascript/navbar.js"></script>
    <link rel="stylesheet"  href="styles/indexStyle.css">

    <div id="heading-for-small-screen">
        <div id="website_Name" onclick="window.location.href='index.php'">HypMonitor <img src="images/navbarImages/heart.svg" alt="Image of heart icon" > </div>
        <div id="openNav" onclick="openSideMenu()">Menu &#9776;</div>
    </div>


    <div id="navbar">

        <div id="sidebarclose" onclick="openSideMenu()">X</div>
        <div id="website_Name" onclick="window.location.href='index.php'">HypMonitor <img src="images/navbarImages/heart.svg" alt="Image of heart icon" > </div>

        <div id="navMenu">
            <div id="home_option" onclick="window.location.href='index.php'"> Home</div>
            <div id="profile_option" > Profile</div>
            <div id="recordBP_option" onclick="window.location.href='recordBP.php'" > Record BP</div>
            <div onclick="window.location.href='support_network.php'">Support</div>
            <div onclick="window.location.href='about_us.php'"> About Us</div>
        </div>

        <div id="register_and_login_container">
            <div id="register_container" onclick="window.location.href='create_account.php' ">Create an account</div> 
            <div id="login_container" onclick="window.location.href='login.php'" >Login</div>
            <div id="user_logged_in" onclick="showDropdown()"> <img id="profilephoto" src="images/profilePhoto/unisex.png" alt="" width="35px"> <span id="username_span"> <?php echo isset($_SESSION["loggedIn_username"])? htmlspecialchars($_SESSION["loggedIn_username"]."") : htmlspecialchars('') ?></span> <img id="arrowdown" src="images/profilePhoto/image.png" alt="" width="15px"> </div>
           
            <div id="dropdownContent">
                <p onclick="window.location.href='create_account.php'">Create an account</p>
                <p id="dropdown_lgout" onclick="window.location.href='logout.php'">Logout</p>

            </div>

            <div id="create_another_account_div" onclick="window.location.href='create_account.php'">Create another account</div>
        </div>

    </div>
    
</header>



