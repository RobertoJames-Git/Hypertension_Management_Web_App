<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Blood Pressure</title>
    <link rel="stylesheet" href="styles/recordBPStyles.css">
</head>
<body>
    
    <?php
        require_once("navbar.php");
    ?>

    <div id="recordBP_container">
        <h1>Track your blood pressure</h1>

        <p>Please enter today's readings</p>
        <form action="Process/validate_recordBP.php" method="post" >


            <label for="">Systolic</label>
            <label for="">Diastolic</label>
            <input class="RecordBP_InputField" type="number" placeholder="Enter value" name="systolic">
            <input class="RecordBP_InputField" type="number" name="diastolic" id="" placeholder="Enter value">

            <label for="">Pulse / Heart Rate</label>
            <div></div><!--Acting as a empty place holder for the grid -->
            <input class="RecordBP_InputField" type="number" name="heart_rate" id="" placeholder="Enter value" >
            <div></div><!--Acting as a empty place holder for the grid -->

            <label for="">Time</label>
            <label for="">Date</label>
            

            <input class="RecordBP_InputField" type="time" name="time" id="">
            <input class="RecordBP_InputField" type="date">


            <input type="submit"value="Confirm Reading" name="bp_record">
            <input type="reset" value="Cancel">
            
        </form>

        <button id="edit_prev_readings">Edit Previous Readings</button>
    </div>
</body>
</html>