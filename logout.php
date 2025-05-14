<?php
    session_start();

    // Preserve only selected session variables
    $preserveKeys = ["unlockTime", "remainingMillis", "login_attempts"];
    $tempSession = [];

    foreach ($preserveKeys as $key) {
        if (isset($_SESSION[$key])) {
            $tempSession[$key] = $_SESSION[$key];
        }
    }

    // Unset all session variables
    session_unset();

    // Restore the preserved session variables
    $_SESSION = $tempSession;

    header("location:index.php");
    exit();
?>