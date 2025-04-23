

<?php
    // Get the filename of the current page to apply styling to specific nav bar to indicate what option was selected
    $currentPage = basename($_SERVER['PHP_SELF']);
?>

<div id="Support_Navbar">
    <!-- Apply styling if the current page is 'support_network.php' -->
    <div class="dropdown" <?php echo ($currentPage === 'support_network.php') ? 'id="support_selected"' : ''; ?>>
        Manage Support Network
        <div class="dropdown_content">
            <a onclick="supportNetworkOption('support_network_ID')" >View Support Network</a>

            <?php
                #Customize message based on the type of user that has logged in
                if ($_SESSION["userType"]==="Patient"){
                    $dropdown_Search_msg="Add to Support Network";
                }
                else if ($_SESSION["userType"]==="Health Care Professional" || $_SESSION["userType"]==="Family Member") {
                    $dropdown_Search_msg="Search for Patient";
                }
            ?>
            <a onclick="supportNetworkOption('add_supp_net_container')"><?php echo htmlspecialchars($dropdown_Search_msg)?></a>
            <a onclick="supportNetworkOption('pending_container')">Pending Requests</a>
            <a onclick="supportNetworkOption('rejected_container')">Rejected Requests</a>
        </div>
    </div>
    <!-- Apply styling if the current page is 'chat.php' -->
    <div class="dropdown" <?php echo ($currentPage === 'chat.php') ? 'id="support_selected"' : ''; ?>>
        Chat with
        <div class="dropdown_content" id="chat-with-dropdown">
            <a onclick="startChat('Family Member')">Family Member</a>
            <a onclick="startChat('Health Care Professional')">Health Care Professional</a>
            <a onclick="startChat('Patient')">Patient</a>
        </div>
    </div>


</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    // Get the current page filename
    let currentPage = window.location.pathname.split("/").pop();

    // Get all dropdown divs
    let dropdowns = document.querySelectorAll("#Support_Navbar .dropdown");

    // Get the 'Chat with' dropdown content
    const chatDropdown = document.getElementById("chat-with-dropdown");

    // Get all the links inside the 'Chat with' dropdown
    const chatLinks = chatDropdown.querySelectorAll("a");

    // Determine which links to show/hide based on userType
    const userType = "<?php echo htmlspecialchars($_SESSION["userType"]); ?>";

    if (userType === "Patient") {

      // Show chat option for Patient
      chatLinks.forEach(link => {
        if (link.textContent.trim() === "Patient") {
          link.style.display = "none";
        } else {
          link.style.display = "block";
        }
      });
    } 
    
    //Show chat option for Family Member and Health Care Professional
    else if (userType === "Family Member" || userType === "Health Care Professional") {
      chatLinks.forEach(link => {
        if (link.textContent.trim() !== "Patient") {
          link.style.display = "none";
        } else {

          //change the html text in drop down from Patient to Family mmber if the user Type is Family Member
          if(userType == "Family Member" && link.innerHTML =="Patient"){
            link.innerHTML ="Family Member"
          }
          link.style.display = "block"; 
        }
      });
    }
  });
</script>
