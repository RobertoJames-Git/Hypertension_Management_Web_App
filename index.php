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
            <div id="register_in_about_container">Get Started Now</div> 
            <div id="aboutus_container">About Us</div> 
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

                <p>Daily Reminders: Receive alerts to upload your blood pressure readings every day.</p>
                <p>Emergency Notifications: Instantly notify family members and healthcare professionals if your blood pressure is too high.</p>

            </div>
        </div>


        <div>
            <div class="image-wrapper">
                <img src="images/indexImages/monitor.png" alt="">
            </div>

            <div id="monitor_info">
                
                <h2>Monitor</h2>
                
                <p>Visual Tracking: View an interactive graph of your blood pressure readings.</p>
                <p>History Access: Easily access your past readings to monitor trends over time.</p>
                <p>Informed Decisions: Share your data with healthcare professionals for personalized care and improved management.</p>
            </div>
        </div>


        <div>

            <div class="image-wrapper">
                <img src="images/indexImages/support.png" alt="" >
            </div>
            
            <div id="support_info">
                <h2>Support</h2>
                
                <p>Add Support Members: Include family members and healthcare professionals in your care team.</p>
                <p>Collaborative Care: Allow your support network to monitor your blood pressure readings and provide encouragement.</p>
                <p>Patient-Centered Messaging: Allow patients to communicate directly with their family members and healthcare professionals.</p>
            
            </div>
        </div>


    </div>   

</body>
</html>