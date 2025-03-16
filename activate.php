<?php 
    require_once("Database/database_actions.php");
    require_once("Process/sendmail.php");

    //unset variable that will be used to display errors
    unset($_SESSION["database_or_sendmail_Err"]);

    $result=activateAccount($_GET["token"]);

    if(strpos($result,"Activation link expired")!==false){

        //if token and password ws changes successfully then a email will be send to the user
        if(modifyTokenAndPassword($_GET["token"])==true){
            sendActivationEmail($_SESSION['email']);
        };
        
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Activation</title>
    
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let element = document.getElementById("tokenResult");
            
            if (element.textContent.trim().includes("Invalid",0)|| element.textContent.trim().includes("expired",0)) { // Check if the element exists
                let result = element; // Use textContent instead of innerHTML
                element.style="color:red;";
            } else {
                element.style="color:green;";
            }
        });
    </script>

</head>
<body>

    <style>
        #tokenAlertContainer{
            width:380px;
            text-align: center;
            background-color: white;
            outline: 1px solid black;
            /* Centering */
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            box-shadow: 0px 0px 8px 2px black;
            
        }


        body{
            font-family: sans-serif;
            background-color: rgb(230, 230, 240);
            color: black;
        }  
        #tokenAlertMessage{

            padding:10px;
        } 

        #backtohome_link{
            background-color: #1946da;
            cursor: pointer;
            color: white;
            padding: 10px;
            
        }

        #tokenResult{
            font-weight: 600;
        }

    </style>



    <div id="tokenAlertContainer">

        <div id="tokenAlertMessage">


            <h1>Account Activation</h1>

            <span id="tokenResult"><?php 

                echo !isset($_SESSION["database_or_sendmail_Err"])? htmlspecialchars("$result"): htmlspecialchars($_SESSION["database_or_sendmail_Err"]);
            ?>
            </span>

        </div>

        <div id="backtohome_link" onclick="window.location.href='login.php'">Go to Login</div>
    </div>

</body>
</html>