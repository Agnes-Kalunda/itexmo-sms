# Itexmo SMS Gateway for Laravel

**Itexmo SMS Gateway Integration for Laravel** is a package that allows you to send SMS messages using the Itexmo API in your Laravel applications. It provides a simple and clean API for sending text messages, checking SMS credits, and broadcasting messages to multiple recipients.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Sending SMS](#sending-sms)
  - [Checking SMS Balance](#checking-sms-balance)
  - [Broadcasting SMS with OTP](#broadcasting-sms-with-otp)
  - [Sending Two-Dimensional Broadcast](#sending-two-dimensional-broadcast)
- [Available Endpoints](#available-endpoints)
- [Error Handling](#error-handling)
- [Testing the Package](#testing-the-package)

## Installation

You can install the package via Composer:

```bash
composer require itexmo/itexmo-sms
```

After installation, the package will automatically register its service provider and facade.

## Configuration

To use the Itexmo package, you need to set up your Itexmo credentials in the Laravel environment configuration file (.env).

Add the following lines to your `.env` file:

```env
# Mandatory settings
ITEXMO_API_CODE=your_itexmo_api_code            # Replace with your Itexmo API code
ITEXMO_EMAIL=your_itexmo_account_email          # Replace with your Itexmo account email
ITEXMO_PASSWORD=your_itexmo_account_password    # Replace with your Itexmo account password

# Optional settings (default values will be used if not set)
ITEXMO_BASE_URL=https://api.itexmo.com/api/     # Base URL for the Itexmo API (default: https://api.itexmo.com/api/)
ITEXMO_RETRY_ATTEMPTS=3                         # Number of retry attempts if API request fails (default: 3)
ITEXMO_RETRY_DELAY=5                            # Delay between retry attempts in seconds (default: 5)
```

Next, publish the package configuration:

```bash
php artisan vendor:publish --tag=itexmo-config
```

This will create a configuration file `config/itexmo.php` where you can manage your Itexmo settings.

## Usage

### Sending SMS

To send an SMS message to a single or multiple recipients, use the `broadcast()` method provided by the `ItexmoSms` class.

Example:

```php
use Itexmo\ItexmoSms\ItexmoSms;

$itexmo = app(ItexmoSms::class);
$recipients = ['number_1_here', 'number_2_here']; 
$message = 'Hello from Itexmo!';

$response = $itexmo->broadcast($recipients, $message);

if ($response['success']) {
    echo "SMS sent successfully!";
} else {
    echo "SMS sending failed: " . $response['message'];
}
```

### Checking SMS Balance

You can check your current SMS balance using the `checkBalance()` method. This will return the remaining credits in your Itexmo account.

Example:

```php
$itexmo = app(ItexmoSms::class);
$response = $itexmo->checkBalance();

if ($response['success']) {
    echo "Your balance is: " . $response['data']['Balance'];
} else {
    echo "Failed to check balance: " . $response['message'];
}
```

### Broadcasting SMS with OTP

To send OTP (One-Time Password) to a single or multiple recipients, you can use the `broadcastOTP()` method.

Example:

```php
$recipients = ['number_1_here', 'number_2_here'];
$message = 'Your OTP is 123456.';

$response = $itexmo->broadcastOTP($recipients, $message);

if ($response['success']) {
    echo "OTP sent successfully!";
} else {
    echo "Failed to send OTP: " . $response['message'];
}
```

### Sending Two-Dimensional Broadcast

To send multiple messages to different recipients, you can use the `broadcast2d()` method. This allows you to send different messages to different phone numbers.

Example:

```php
$messages = [
    ['number_1_here', 'Hello John!'],
    ['number_2_here', 'Hello Jane!']
];

$response = $itexmo->broadcast2d($messages);

if ($response['success']) {
    echo "Messages sent successfully!";
} else {
    echo "Failed to send messages: " . $response['message'];
}
```

## Available Endpoints

1. **Broadcast SMS** (`broadcast`)
   - Sends an SMS message to multiple recipients.
   - Parameters:
     - `recipients` (array): An array of phone numbers.
     - `message` (string): The message to send.
   - Example:
     ```php
     $itexmo->broadcast(['number_1_here', 'number_2_here'], 'Hello!');
     ```

2. **Check Balance** (`query`)
   - Retrieves the current SMS balance for the account.
   - Parameters: None
   - Example:
     ```php
     $itexmo->checkBalance();
     ```

3. **Broadcast OTP** (`broadcast-otp`)
   - Sends an OTP (One-Time Password) to multiple recipients.
   - Parameters:
     - `recipients` (array): An array of phone numbers.
     - `message` (string): The OTP message to send.
   - Example:
     ```php
     $itexmo->broadcastOTP(['number_1_here', 'number_2_here'], 'Your OTP is 123456');
     ```

4. **Two-Dimensional Broadcast** (`broadcast-2d`)
   - Sends different messages to different recipients.
   - Parameters:
     - `messages` (array): A two-dimensional array where each element contains a recipient number and the message.
   - Example:
     ```php
     $messages = [
         ['number_1_here', 'Hello John!'],
         ['number_2_here', 'Hello Jane!']
     ];
     $itexmo->broadcast2d($messages);
     ```

## Error Handling

All responses from the Itexmo API are handled within the methods. The responses are returned as arrays with the following keys:

- `success`: Indicates if the request was successful (true or false).
- `message`: Contains a description of the result or error.
- `data`: Contains the raw API response (if applicable).

You can easily check if a request was successful by checking the `success` key.

Example:

```php
$response = $itexmo->broadcast(['number_1_here'], 'Test message');

if ($response['success']) {
    echo "SMS sent!";
} else {
    echo "Error: " . $response['message'];
}
```

## Testing the Package

You can test the package using Postman or any other API testing tool. Simply set up an endpoint that triggers one of the available methods (e.g., `broadcast`, `checkBalance`) and call it via an HTTP request.

### Example API Route for SMS Broadcast

```php
use Illuminate\Http\Request;
use Itexmo\ItexmoSms\ItexmoSms;

Route::post('/send-sms', function (Request $request) {
    $itexmo = app(ItexmoSms::class);
    $recipients = $request->input('recipients'); // an array of phone numbers
    $message = $request->input('message');

    $response = $itexmo->broadcast($recipients, $message);

    return response()->json($response);
});
```

### Testing via Postman

- URL: `http://your-app-url/send-sms`
- Method: POST
- Body: application/json
```json
{
  "recipients": ["number_1_here", "number_2_here"],
  "message": "Hello from Itexmo!"
}
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

