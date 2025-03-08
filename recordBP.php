

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Blood Pressure</title>
    <link rel="stylesheet" href="styles/recordBPStyles.css">
    <script src="Javascript/recordBP.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
</head>
<body>

    <?php
        require_once("navbar.php");

        // Check if user is logged in
        if (!isset($_SESSION["loggedIn_username"])) {
            header("location:login.php");
            exit();
        }
    ?>

    <div id="database_Error">
        <span id="errorContent"><?php  echo isset($_SESSION["dbMessage"]) ? htmlspecialchars($_SESSION["dbMessage"]) :'';  unset($_SESSION["dbMessage"]) //ensure result is only shown once?></span>
    </div>

    <div id="recordBP_container">
        <h1>Track your blood pressure</h1>

        <p>Please enter today's readings</p>
        <form action="Process/validate_recordBP.php" method="post" >


            <label for="">Systolic (mmHg)  <span class="errMessage"><br><?php echo isset($_SESSION["systolicErr"]) ? htmlspecialchars($_SESSION["systolicErr"]) : htmlspecialchars("") ?></span> </label>
            <label for="">Diastolic (mmHg) <span class="errMessage"><br><?php echo isset($_SESSION["diastolicErr"]) ? htmlspecialchars($_SESSION["diastolicErr"]) : htmlspecialchars("") ?></span></label>
            <input class="RecordBP_InputField" type="number" placeholder="Enter value" name="systolic"          value="<?php echo isset($_SESSION["systolic"]) ? htmlspecialchars($_SESSION["systolic"]) : htmlspecialchars("") ?>" required>
            <input class="RecordBP_InputField" type="number" name="diastolic" id="" placeholder="Enter value"   value="<?php echo isset($_SESSION["diastolic"]) ? htmlspecialchars($_SESSION["diastolic"]) : htmlspecialchars("") ?>"  required>

            <label for="">Pulse / Heart Rate (BPM) <span class="errMessage"><br><?php echo isset($_SESSION["heart_rateErr"]) ? htmlspecialchars($_SESSION["heart_rateErr"]) : htmlspecialchars("") ?></span> </label>
            <label for="">Date  <span class="errMessage"><br><?php echo isset($_SESSION["dateErr"]) ? htmlspecialchars($_SESSION["dateErr"]) : htmlspecialchars("") ?></span> </label>
            <input class="RecordBP_InputField" type="number" name="heart_rate" id="" placeholder="Enter value"  value="<?php echo isset($_SESSION["heart_rate"]) ? htmlspecialchars($_SESSION["heart_rate"]) : htmlspecialchars("") ?>" required>
            <input class="RecordBP_InputField" type="date" name="date"  value="<?php echo isset($_SESSION["date"]) ? htmlspecialchars($_SESSION["date"]) : htmlspecialchars("") ?>" required>


            <input type="submit"value="Confirm Reading" name="bp_record">
            <input type="reset" value="Cancel">
            
        </form>

        <button id="edit_prev_readings">Edit Previous Readings</button>
    </div>

    

    <?php

        require_once("Database/database_actions.php");
        $page = isset($_GET["page"]) ? intval($_GET["page"]) : 1; // Default page 1 (most recent 7)
        $readings = getBloodPressureReadings($_SESSION["loggedIn_username"],$page);


        // Extract data for Chart.js
        $dates = [];
        $systolic = [];
        $diastolic = [];
        $heartRate = [];

        /*if records were found and there were no errors then they will be stored in the variables 
        to be displayed in the chart*/

        if (!isset($readings["error"])){

            foreach ($readings as $reading) {
                $dates[] = $reading["readingdate"];
                $systolic[] = $reading["systolic"];
                $diastolic[] = $reading["diastolic"];
                $heartRate[] = $reading["heart_rate"];
            }

        }


        $nextPage = $page + 1;
        $prevPage = $page > 1 ? $page - 1 : 1;

        $recordMsg="";
        if(empty($dates) && empty($systolic) && empty($diastolic) && empty($heartRate)){
            $recordMsg="No records Found";
        }
    ?>

    
    <div id="graph_container">
        <br>
        <h1>Blood Pressure Chart</h1>

        <div>
            <button onclick="fetchReadings(<?php echo htmlspecialchars($prevPage); ?>)">Previous Readings</button>
            <button onclick="fetchReadings(<?php echo htmlspecialchars($nextPage); ?>)" <?php echo $recordMsg=="" ?  "" : "disabled"?> >Next Readings</button>
            
            <p id="records_alert">

            <?php
                    echo isset($readings["error"]) ? htmlspecialchars($readings["error"]) : "" ;
            ?>

            </p>
        </div>

        <canvas id="bpChart"></canvas> <!-- Graph container -->
    </div>

    <script>
        const ctx = document.getElementById('bpChart').getContext('2d');

        // Reverse the dates and corresponding data arrays
        const reversedDates = <?php echo json_encode($dates); ?>.reverse(); // Reverse the labels (dates)
        const reversedSystolic = <?php echo json_encode($systolic); ?>.reverse(); // Reverse systolic readings
        const reversedDiastolic = <?php echo json_encode($diastolic); ?>.reverse(); // Reverse diastolic readings
        const reversedHeartRate = <?php echo json_encode($heartRate); ?>.reverse(); // Reverse heart rate readings

        const data = {
            labels: reversedDates, // X-axis (Dates)
            datasets: [
                {
                    label: 'Systolic (mmHg)',
                    data: reversedSystolic,
                    borderColor: 'red',
                    backgroundColor: 'rgba(255, 0, 0, 0.2)',
                    fill: false
                },
                {
                    label: 'Diastolic (mmHg)',
                    data: reversedDiastolic,
                    borderColor: 'blue',
                    backgroundColor: 'rgba(0, 0, 255, 0.2)',
                    fill: false
                },
                {
                    label: 'Heart Rate (bpm)',
                    data: reversedHeartRate,
                    borderColor: 'green',
                    backgroundColor: 'rgba(0, 255, 0, 0.2)',
                    fill: false
                }
            ]
        };

        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Measurement'
                        },
                        beginAtZero: false
                    }
                }
            }
        };

        new Chart(ctx, config);

        function fetchReadings(page) {
            window.location.href = "recordBP.php?page=" + page; // Reload with new page number
        }
    </script>


</body>
</html>

