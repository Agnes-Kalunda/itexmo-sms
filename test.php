
<?php 
require 'vendor/autoload.php';

use Agnes\ItexmoSms\ItexmoSms;

// itexmo credentials
$config = [
    'email' => 'your_email@example.com',
    'password' => 'your_password',
    'api_code' => 'your_api_code',
];

// instance of itexmo class
$itexmoSms = new ItexmoSms($config);

echo "Testing Broadcast..\n";

$response = $itexmoSms->broadcast(['12345678909'], 'Test message from sample');
print_r($response);




?>

