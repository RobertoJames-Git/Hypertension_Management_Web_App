<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles/createAccount.css">
    <link rel="stylesheet" href="styles/loginStyle.css">
</head>
<body>


    
    <div id="database_Error">
        <span id="errorContent">Database Error: <?php  echo isset($_SESSION["database_or_sendmail_Err"]) ? htmlspecialchars($_SESSION["database_or_sendmail_Err"]) :''; ?></span>
    </div>
    
    
    <div id="form_container">

        <div>
            <img src="images/loginImages/image[cropped].png" alt="Image of hypertensive family member and support network" >
        </div>
        
        <div id="form_div">
            <br><br><br><br><br>
            <h1> Login</h1>
            
            <form action="Process/" method="post">

                <div class="label_and_alert_containert">
                    <label for="">Username</label>
                    <!-- nl2br() → Converts newlines (\n) into <br> tags, allowing line breaks to be displayed properly in HTML.-->
                    <span class="formError"><?php echo isset($_SESSION["usernameErr"]) ? htmlspecialchars($_SESSION["usernameErr"], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                </div>

                <input type="text" name="username" id="" value="<?php echo isset($_SESSION["username"]) ? htmlspecialchars($_SESSION["username"],ENT_QUOTES,'UTF-8'):'' ?>"  >

                <div class="label_and_alert_containert">
                    <label for="">Password</label>
                    <span class="formError"><?php echo isset($_SESSION["passwordErr"]) ? htmlspecialchars($_SESSION["passwordErr"], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                </div>
                <input type="password" name="password" id="" value="<?php echo isset($_SESSION["password"]) ? htmlspecialchars($_SESSION["password"],ENT_QUOTES,'UTF-8'):'' ?>" >
         
                
                <input id="" class="button_style" type="submit" name="account_lgin" value="Login">

            </form>

            <button id="" class="button_style" onclick="window.location.href='index.php'" >Back to Home</button>
            <p>Dont already have an account? <a href="create_account.php">Sign up</a> </p>
        </div>
   
    </div>


</body>
</html>