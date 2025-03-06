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

        <p>Please enter your readings</p>
        <form action="" method="post">


            <label for="">Systolic</label>
            <label for="">Diastolic</label>
            <input class="RecordBP_InputField" type="number" placeholder="Enter value">
            <input class="RecordBP_InputField" type="number" name="" id="" placeholder="Enter value">
            <label for="">Time</label>
            <label for="">Date</label>
            

            <input class="RecordBP_InputField" type="time" name="" id="">
            <input class="RecordBP_InputField" type="date">


            <input type="submit"value="Confirm Reading" name="">
            <input type="reset" value="Cancel">
            
        </form>

        <button id="edit_prev_readings">Edit Previous Readings</button>
    </div>
</body>
</html>