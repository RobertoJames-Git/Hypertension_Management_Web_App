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
    ?>

    <div id="form_container">

        <div>
            <img src="images/CreateAccountImages/hyp_patient_and support.png" alt="" >
        </div>
        
        <div>

            <h1> Create your account</h1>
            
            <form action="" method="post">

                <label for="">First Name</label>
                <input type="text" name="fname" id="">
                <label for="">Last Name</label>
                <input type="text" name="lname" id="">
                <label for="">Date of Birth</label>
                <input type="text" name="dob" id="">
                <label for="">Select your account type</label>
                <select name="user_type" id="">
                    <option value="">Select an option</option>
                    <option value="">Hypertensive Individual</option>
                    <option value="">Family Member</option>
                    <option value="">Healthcare Professional</option>
                </select>
                <label for="">Email</label>
                <input type="email" name="email" id="">
                <label for="">Password</label>
                <input type="password" name="pass">

                <br>
                <input id="submit-btn" type="submit" value="Create Account">


            </form>
        </div>
   
    </div>
</body>
</html>