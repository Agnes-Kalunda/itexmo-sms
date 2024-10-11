<?php
require 'vendor/autoload.php';

// Load environment variables from the .env file
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

// Get the API code, email, and password from the environment
$config = [
    'api_code' => getenv('ITEXMO_API_CODE'),
    'email' => getenv('ITEXMO_EMAIL'), // Add this line
    'password' => getenv('ITEXMO_PASSWORD'), // Add this line
];

$itexmo = new Agnes\ItexmoSms\ItexmoSms($config);

// Sending an SMS
$response = $itexmo->broadcast('recipient-phone-number', 'Test message via Itexmo.');

if ($response['success']) {
    echo "SMS sent successfully!";
} else {
    echo "Failed to send SMS: " . $response['message'];
}


?>
