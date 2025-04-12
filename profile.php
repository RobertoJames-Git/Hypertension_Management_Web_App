

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Blood Pressure</title>
    <link rel="stylesheet" href="styles/profileStyle.css">
</head>
<body>

    <?php
        require_once("navbar.php");

        //ensure the user is logged in
        if(!isset($_SESSION["loggedIn_username"],$_SESSION["userType"])|| $_SESSION["loggedIn_username"]==""||$_SESSION["userType"]==""){
            header("Location:logout.php");
            exit();
        }

        require_once("Database/database_actions.php");

        $userDetails= getUserDetailsByUsername();

    ?>



    <div id="profile_container">
        
        <div id="profile_image_and_heading">
            <img src="images/profilePhoto/profile.jpg" alt="" width="400px" >

            <div id="fullname"> <?php echo htmlspecialchars($userDetails['full_name']) ?> </div>
            <div id="email"> <?php echo htmlspecialchars($userDetails['email']) ?> </div>
        </div>

        <div id="profile_content">
            <div class="profile_details"><span>Username : </span>    <span><?php echo htmlspecialchars($_SESSION["loggedIn_username"]) ?></span> </div>
            <div class="profile_details"><span>Gender : </span>    <span><?php echo htmlspecialchars($userDetails['gender']) ?></span> </div>
            <div class="profile_details"><span>DOB : </span>      <span><?php echo htmlspecialchars($userDetails['dob']) ?></span></div>
            <div class="profile_details"><span>User Type : </span>  <span> <?php echo htmlspecialchars($_SESSION["userType"]); ?> </span></div>
            <div class="profile_details"><span>Phone number : </span>  <span> <?php echo htmlspecialchars($userDetails["phone_number"]); ?> </span></div>
        </div>



    </div>


</body>
</html>