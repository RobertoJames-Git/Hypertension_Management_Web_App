<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="stylesheet" href="styles/createAccount.css">
</head>
<body>

    <?php 
        require_once('navbar.php');
        session_start();
    ?>

    <div id="form_container">

        <div>
            <img src="images/CreateAccountImages/hyp_patient_and support.png" alt="" >
        </div>
        
        <div>

            <h1> Create your account</h1>
            
            <form action="Process/verify_create_account.php" method="post">

                <div class="label_and_alert_containert">
                    <label for="">First Name</label>
                    <!-- nl2br() → Converts newlines (\n) into <br> tags, allowing line breaks to be displayed properly in HTML.-->
                    <span class="formError"><?php echo isset($_SESSION["fnameErr"]) ? nl2br(htmlspecialchars($_SESSION["fnameErr"]."\n", ENT_QUOTES, 'UTF-8')) : ''; ?></span>
                </div>
                <input type="text" name="fname" id="">

                <div class="label_and_alert_containert">
                    <label for="">Last Name</label>
                    <span class="formError"><?php echo isset($_SESSION["lnameErr"]) ? nl2br(htmlspecialchars($_SESSION["lnameErr"]."\n", ENT_QUOTES, 'UTF-8')) : ''; ?></span>
                </div>
                <input type="text" name="lname" id="">
                
                <div class="label_and_alert_containert">
                    <label for="">Date of Birth</label>
                    <span class="formError"><?php echo isset($_SESSION["dobErr"]) ? nl2br(htmlspecialchars($_SESSION["dobErr"]."\n", ENT_QUOTES, 'UTF-8')) : ''; ?></span>
                </div>
                <input type="date" name="dob" id="">
                
                <div class="label_and_alert_containert">
                    <label for="">Select your account type</label>
                    <span class="formError"><?php echo isset($_SESSION["user_typeErr"]) ? nl2br(htmlspecialchars($_SESSION["user_typeErr"]."\n", ENT_QUOTES, 'UTF-8')) : ''; ?></span>
                </div>
                <select name="user_type" id="">
                    <option value="">Select an option</option>
                    <option value="">Hypertensive Individual</option>
                    <option value="">Family Member</option>
                    <option value="">Healthcare Professional</option>
                </select>

                <div class="label_and_alert_containert">
                    <label for="">Email</label>
                    <span class="formError"><?php echo isset($_SESSION["emailErr"]) ? nl2br(htmlspecialchars($_SESSION["emailErr"]."\n", ENT_QUOTES, 'UTF-8')) : ''; ?></span>
                </div>
                <input type="" name="email" id="">

                <br>
                <input id="submit-btn" type="submit" name="account_creation" value="Create Account">


            </form>
        </div>
   
    </div>
</body>
</html>