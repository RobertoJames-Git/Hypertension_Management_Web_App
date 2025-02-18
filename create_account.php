<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="stylesheet" href="styles/createAccount.css">
    <script src="Javascript/createAccount.js"></script>
</head>
<body>


    <?php 
        require_once('navbar.php');
        session_start();
    ?>

    <div id="form_container">

        <div>
            <img src="images/CreateAccountImages/hyp_patient_and_support_crop.jpg" alt="Image of hypertensive family member and support network" >
        </div>
        
        <div id="form_div">

            <h1> Create your account</h1>
            
            <form action="Process/verify_create_account.php" method="post">

                <div class="label_and_alert_containert">
                    <label for="">First Name</label>
                    <!-- nl2br() → Converts newlines (\n) into <br> tags, allowing line breaks to be displayed properly in HTML.-->
                    <span class="formError"><?php echo isset($_SESSION["fnameErr"]) ? htmlspecialchars($_SESSION["fnameErr"], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                </div>

                <input type="text" name="fname" id="" value="<?php echo isset($_SESSION["fname"]) ? htmlspecialchars($_SESSION["fname"],ENT_QUOTES,'UTF-8'):'' ?>"  >

                <div class="label_and_alert_containert">
                    <label for="">Last Name</label>
                    <span class="formError"><?php echo isset($_SESSION["lnameErr"]) ? htmlspecialchars($_SESSION["lnameErr"], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                </div>
                <input type="text" name="lname" id="" value="<?php echo isset($_SESSION["lname"]) ? htmlspecialchars($_SESSION["lname"],ENT_QUOTES,'UTF-8'):'' ?>" >
                
                <div class="label_and_alert_containert">
                    <label for="">Gender</label>
                    <span class="formError"><?php echo isset($_SESSION["genderErr"]) ? htmlspecialchars($_SESSION["genderErr"], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                </div>


                <select name="gender" id="">
                    <option value=""        <?php echo isset($_SESSION["gender"])&& $_SESSION["gender"]=="" ? htmlspecialchars("selected") :''?>>Select an option</option>
                    <option value="male"    <?php echo isset($_SESSION["gender"])&& $_SESSION["gender"]=="male" ? htmlspecialchars("selected") :''?> >Male</option>
                    <option value="female"  <?php echo isset($_SESSION["gender"])&& $_SESSION["gender"]=="female" ? htmlspecialchars("selected") :''?>>Female</option>
                    <option value="other"   <?php echo isset($_SESSION["gender"])&& $_SESSION["gender"]=="other" ? htmlspecialchars("selected") :''?>>Other</option>
                    <option value="rather not say"  <?php echo isset($_SESSION["gender"])&& $_SESSION["gender"]=="rather not say" ? htmlspecialchars("selected") :''?> >Rather not say</option>
                </select>
                
                <div class="label_and_alert_containert">
                    <label for="">Date of Birth</label>
                    <span class="formError"><?php echo isset($_SESSION["dobErr"]) ? htmlspecialchars($_SESSION["dobErr"], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                </div>
                <input type="date" name="dob" id=""  value="<?php echo isset($_SESSION["dob"]) ? htmlspecialchars($_SESSION["dob"]) :''?>">

                <div class="label_and_alert_containert" onload="showAfterRefresh()">
                    <label for="">Select your account type</label>
                    <span class="formError"><?php echo isset($_SESSION["user_typeErr"]) ? htmlspecialchars($_SESSION["user_typeErr"], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                </div>
                <select name="user_type" id="user_typeID" onchange="showAttributes(this.value)">
                    <option value="">Select an option</option>
                    <option value="Hypertensive Individual" <?php echo isset($_SESSION["user_type"])&& $_SESSION["user_type"]=="Hypertensive Individual" ? htmlspecialchars("selected") :''?>>Hypertensive Individual</option>
                    <option value="Family Member"           <?php echo isset($_SESSION["user_type"])&& $_SESSION["user_type"]=="Family Member" ? htmlspecialchars("selected") :''?> >Family Member</option>
                    <option value="Healthcare Professional" <?php echo isset($_SESSION["user_type"])&& $_SESSION["user_type"]=="Healthcare Professional" ? htmlspecialchars("selected") :''?>>Healthcare Professional</option>
                </select>
                

                <div class="label_and_alert_containert">
                    <label for="">Email</label>
                    <span class="formError"><?php echo isset($_SESSION["emailErr"]) ? htmlspecialchars($_SESSION["emailErr"], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                </div>
                <input type="email" name="email" id="" value="<?php echo isset($_SESSION["email"]) ? htmlspecialchars($_SESSION["email"]) :''?>">

                <div class="label_and_alert_containert family_attribute">
                    <label for="">Education Level</label>
                    <span class="formError"><?php echo isset($_SESSION["family_edu_level_Err"]) ? htmlspecialchars($_SESSION["family_edu_level_Err"], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                </div>

                <select name="family_edu_level" id="" class="family_attribute">
                    <option value="">Select an option</option>
                    <option value="No Formal Education" <?php echo isset($_SESSION["family_edu_level"])&& $_SESSION["family_edu_level"]=="No Formal Education" ? htmlspecialchars("selected") :''?>>No Formal Education</option>
                    <option value="Elementary"          <?php echo isset($_SESSION["family_edu_level"])&& $_SESSION["family_edu_level"]=="Elementary" ? htmlspecialchars("selected") :''?>>Elementary (primary or preparatory school)</option>
                    <option value="Secondary"           <?php echo isset($_SESSION["family_edu_level"])&& $_SESSION["family_edu_level"]=="Secondary" ? htmlspecialchars("selected") :''?>>Secondary (high school or technical school)</option>
                    <option value="Some Tertiary"       <?php echo isset($_SESSION["family_edu_level"])&& $_SESSION["family_edu_level"]=="Some Tertiary" ? htmlspecialchars("selected") :''?>>Some Tertiary Education</option>
                    <option value="Vocational Training" <?php echo isset($_SESSION["family_edu_level"])&& $_SESSION["family_edu_level"]=="Vocational Training" ? htmlspecialchars("selected") :''?>>Vocational Training</option>
                    <option value="Degree"              <?php echo isset($_SESSION["family_edu_level"])&& $_SESSION["family_edu_level"]=="Degree" ? htmlspecialchars("selected") :''?>>Degree (undergraduate or graduate degree)</option>
                </select>


                <div class="label_and_alert_containert health_prof_attribute">
                    <label for="">Years of Experience</label>
                    <span class="formError"><?php echo isset($_SESSION["health_prov_exp_Err"]) ? htmlspecialchars($_SESSION["health_prov_exp_Err"], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                </div>

                <select name="health_prov_exp" id="" class="health_prof_attribute">
                    <option value=""                    <?php echo isset($_SESSION["health_prov_exp"])&& $_SESSION["health_prov_exp"]=="" ? htmlspecialchars("selected") :''?>>Select an Option</option>
                    <option value="Less than a year"    <?php echo isset($_SESSION["health_prov_exp"])&& $_SESSION["health_prov_exp"]=="Less than a year" ? htmlspecialchars("selected") :''?>>Less than a year</option>
                    <option value="One to two years"    <?php echo isset($_SESSION["health_prov_exp"])&& $_SESSION["health_prov_exp"]=="One to two years" ? htmlspecialchars("selected") :''?>>One to two years</option>
                    <option value="Three to Fours years"<?php echo isset($_SESSION["health_prov_exp"])&& $_SESSION["health_prov_exp"]=="Three to Fours years" ? htmlspecialchars("selected") :''?>>Three to Fours years</option>
                    <option value="Five years or more"  <?php echo isset($_SESSION["health_prov_exp"])&& $_SESSION["health_prov_exp"]=="Five years or more" ? htmlspecialchars("selected") :''?>>Five years or more</option>
                    <option value="Over a decade"       <?php echo isset($_SESSION["health_prov_exp"])&& $_SESSION["health_prov_exp"]=="Over a decade" ? htmlspecialchars("selected") :''?>>Over a Decade</option>
                </select>
                
                <input id="submit-btn" type="submit" name="account_creation" value="Create Account">

            </form>
        </div>
   
    </div>

    <br><br>
</body>
</html>