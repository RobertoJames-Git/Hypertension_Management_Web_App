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
    
    <br>

    <div id="features_container">

        <h1>Features</h1>
        
 
        <div id="alert_container">
            <p> <img src="images/indexImages/alert.png" alt="" width="40px">Alert</p>
            <ul>
                <li>Daily Reminders: Receive alerts to upload your blood pressure readings every day.</li>
                <li>Emergency Notifications: Instantly notify family members and healthcare professionals if your blood pressure is too high.</li>
            </ul>
        </div>



            
        <div id="monitor_container">
            <p><img src="images/indexImages/monitor.png" alt="" width="">Monitor</p>

            <ul>
                <li>Visual Tracking: View an interactive graph of your blood pressure readings.</li>
                <li>History Access: Easily access your past readings to monitor trends over time.</li>
                <li>Informed Decisions: Share your data with healthcare professionals for personalized care and improved management.</li>
            </ul>
        </div>
            

        <div id="support_container">
            <p><img src="images/indexImages/support.png" alt="" >Support</p>
            <ul>
                <li>Add Support Members: Include family members and healthcare professionals in your care team.</li>
                <li>Collaborative Care: Allow your support network to monitor your blood pressure readings and provide encouragement.</li>
                <li>Patient-Centered Messaging: Allow patients to communicate directly with their family members and healthcare professionals.</li>
            </ul>
        </div>

    </div>   


</body>
</html>

