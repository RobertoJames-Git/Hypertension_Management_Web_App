<?php
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        echo("File not found: ". $filePath);
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }

        list($key, $value) = explode('=', $line, 2);
        putenv(trim($key) . '=' . trim($value));
    }
}

function alertSupportNetwork(array $phoneNumbers, $message) {
    
    // Require the bundled autoload file
    require_once 'twilio-php-main/src/Twilio/autoload.php';

    // Load the .env file
    loadEnv(__DIR__ . '/../.env');

    // Get credentials from environment variables
    $sid = getenv('TWILIO_ACCOUNT_SID')??null;
    $token =getenv('TWILIO_AUTH_TOKEN')?? null;
    $twilioPhoneNumber = getenv('TWILIO_PHONE_NUMBER') ?? null;

    // Initialize Twilio client
    $client = new Twilio\Rest\Client($sid, $token);

    $allSuccessful = true; // Track overall success

    foreach ($phoneNumbers as $phoneNumber) {

        // Ensure $phoneNumber is a string
        $phoneNumber = (string) $phoneNumber;
        // Remove dashes but retain the '+' if present
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        


        // Validate phone number format and correct prefixes
        if (preg_match('/^\+?876/', $phoneNumber)) {
            $phoneNumber = '+1876' . substr($phoneNumber, -7); // Ensure correct format
        } elseif (preg_match('/^\+?658/', $phoneNumber)) {
            $phoneNumber = '+1658' . substr($phoneNumber, -7); // Ensure correct format
        } elseif (!preg_match('/^\+1876|^\+1658/', $phoneNumber)) {
            // If number doesn’t start with valid Jamaican prefixes, skip
            $allSuccessful = false;
            continue;
        }

        try {
            // Send the message
            $client->messages->create(
                $phoneNumber,
                [
                    'from' => $twilioPhoneNumber,
                    'body' => $message
                ]
            );
        } catch (Exception $e) {
            // If any message fails, set flag to false
            $allSuccessful = false;
        }
    }

    // Return appropriate message based on success/failure
    return $allSuccessful
        ? [true, "All members in your support network were notified"]
        : [false, "Failed to notify all members of your support network".$e];
}

