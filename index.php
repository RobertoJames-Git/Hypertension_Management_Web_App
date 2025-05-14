<?php
    /* Code by: Roberto James and Brandon Bent
     * 
     */


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomePage</title>
</head>
<body>


    <?php
        require_once("navbar.php");
    ?>
    
    <div id="welcome_message">
        <img src="images/indexImages/patient-with-support-cropped.jpg" alt="" width="600px">
        <div id="welcome_container">
            <p>Empowering Patients, Families, and Healthcare Providers to Manage Hypertension Together</p>
            <div id="register_in_about_container" onclick="window.location.href='create_account.php'">Get Started Now</div> 
            <div id="aboutus_container" onclick="window.location.href='about_us.php'">About Us</div> 
        </div>
    </div>
    <br>

    <!--
    <div id="prevalence_container">
        <h1>Partnering for a better health</h1>
        <img src="images/indexImages/hypertension.jpg" alt="" width="600px">
        <p>Approximately one in three Jamaicans aged 15 and older are hypertensive, with 35.8% of women and 31.7% of men affected<br> (Jamaica Health and Lifestyle Survey, 2016-2017).</p>
    </div>
    -->

    <br><br>

    <div id="features_container">

        <h1>Features Designed to Help You</h1>

        <div id="alert_container">
            <div class="image-wrapper">
                <img src="images/indexImages/alert.png" alt="">
            </div>
            <div id="alert_info">     
                <h2> Alert</h2>

                <p>Sends emergency notifications to your support network if readings are at a critical level.</p>

                <div id="learn_more_container">Learn more</div>
            </div>
        </div>


        <div>
            <div class="image-wrapper">
                <img src="images/indexImages/monitor.png" alt="">
            </div>

            <div id="monitor_info">
                
                <h2>Monitor</h2>
                
                <p>Provides an interactive graph for visual tracking, access to past readings for trend analysis, and data sharing with healthcare professionals.</p>
                <div id="learn_more_container">Learn more</div>
            </div>
        </div>


        <div id="support_container">
            <div class="image-wrapper">
                <img src="images/indexImages/support.png" alt="" >
            </div>
            <div id="support_info">
                <h2>Support</h2>
                <p>Add family members and a healthcare professional to your support network so you can collaboratively monitor your health and provide encouragement.</p>
                
                <div id="learn_more_container">Learn more</div>
            </div>
        </div>


    </div>   

</body>
</html>