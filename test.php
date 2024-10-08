
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


// broadcast2d endpoint test
echo "\nTesting broadcast2d.....\n";
$message = [
    ['Recipient' => '12345678909', 'Message' => 'Message 1'],
    ['Recipient' => '12345678909', 'Message' => 'Message 1']
];

$response = $itexmoSms->broadcast2d($message);
print_r($response);


// broadcastOTP test

echo '\nTestinh boradcastOTP...\n';
$response = $itexmoSms->broadcastOTP('12345678909', 'Your OTP is 123456');
print_r($response);




?>

