


<div id="Support_Navbar">
    <div class="dropdown" onclick="window.location.href='support_network.php'">
        Manage Support Network
        <div class="dropdown_content">
            <a onclick="onlyShow('support_network_ID')" >View Support Network</a>

            <?php
                if ($_SESSION["userType"]==="Patient"){
                    $dropdown_Search_msg="Add to Support Network";
                }
                else if ($_SESSION["userType"]==="Health Care Profession" || $_SESSION["userType"]==="Family Member") {
                    $dropdown_Search_msg="Search for Patient";
                }
            ?>
            <a onclick="onlyShow('add_supp_net_container')"><?php echo htmlspecialchars($dropdown_Search_msg)?></a>
            <a onclick="onlyShow('pending_container')">Pending Requests</a>
            <a onclick="onlyShow('rejected_container')">Rejected Request</a>
        </div>
    </div>
    <div class="dropdown">
        Chat with
        <div class="dropdown_content" id="chat-with-dropdown">
            <a onclick="startChat('Family Member')">Family Member</a>
            <a onclick="startChat('Health Care Professional')">Health Care Professional</a>
        </div>
    </div>


</div>

<script>

    document.addEventListener("DOMContentLoaded", function () {
        // Get the current page filename
        let currentPage = window.location.pathname.split("/").pop();

        // Get all dropdown divs
        let dropdowns = document.querySelectorAll("#Support_Navbar .dropdown");

        dropdowns.forEach(div => {
            let text = div.textContent.trim();

            if (currentPage === "support_network.php" && text.includes("Manage Support Network")) {
                div.id = "support_selected";
            } else if (currentPage === "chat.php" && text.includes("Chat with")) {
                div.id = "support_selected";
            }
        });
    });

</script>

