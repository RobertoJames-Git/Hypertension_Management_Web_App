
<?php
    // Dynamically determine the base URL
    $base_url = dirname($_SERVER['SCRIPT_NAME']) .'/';

    $level_down_count= (substr_count($base_url,'/')-2);
    $level_down='';
    while($level_down_count!=0){
        $level_down_count--;
        $level_down = $level_down .'../';
    }

?>

<header>
    <link rel="stylesheet" href="<?php echo $level_down?>styles/indexStyle.css">
    <div class="navbar">
        <div class="website_Name">HypMonitor <img src="<?php echo $level_down ?>images/navbarImages/heart.svg" alt="Image of heart icon" > </div>
        <div class="navMenu">
            <div>Home</div>
            <div>Profile</div>
            <div>Record BP</div>
            <div>Support</div>
            <div>About Us</div>
        </div>

        <div class="register_and_login_container">
            <div class="register_container">Register</div> 
            <div class="login_container">Login</div>
        </div>
    </div>
</header>