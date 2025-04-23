    function startChat(chatWith) {
        // Redirect to chat.php with a GET parameter for the selected chat option
        const chatUrl = `chat.php?chatWith=${encodeURIComponent(chatWith)}`;
        window.location.href = chatUrl; // Redirect to the new URL
    }

    function supportNetworkOption(containerID) {
        // Redirect to chat.php with a GET parameter for the selected chat option
        const supportNetUrl = `support_network.php?container=${encodeURIComponent(containerID)}`;
        window.location.href = supportNetUrl; // Redirect to the new URL
    }

    
    function onlyShow(containerID) {
        // List of all container IDs
        const allContainer = ['support_network_ID', 'add_supp_net_container', 'pending_container', 'rejected_container'];

        // Iterate through each container in the list
        allContainer.forEach(id => {
            const element = document.getElementById(id);

            if (element) {
                // Show the element if it matches the passed containerID, otherwise hide it
                if (id === containerID) {
                    element.style.display = "block"; // Display this container
                } else {
                    element.style.display = "none"; // Hide all others
                }
            }
        });
    }
