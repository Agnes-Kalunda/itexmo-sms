# Itexmo SMS Gateway Integration for Laravel 5.8

This package provides an easy-to-use integration of the Itexmo SMS Gateway into Laravel 5.8 applications. It simplifies the process of sending SMS messages via the Itexmo API, handling configuration, and managing responses.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Error Handling](#error-handling)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Requirements

- PHP 7.1.3 or higher
- Laravel 5.8
- Composer

## Installation

You can install this package via Composer. Run the following command in your terminal:

```bash
composer require agnes/itexmo-sms
```

After installation, publish the configuration file:

```bash
php artisan vendor:publish --tag=itexmo-config
```

## Configuration

After publishing the configuration file, you can find it at `config/itexmo.php`. Edit this file to add your Itexmo configuration file variables:

```php
return [
    'api_key' => env('ITEXMO_API_KEY', ''),
    // Other configuration options...
];
```

Make sure to add your Itexmo API key to your `.env` file:

```
ITEXMO_API_KEY=your_api_key_here
```

## Usage

To send an SMS using this package:

```php
use Agnes\ItexmoSms\ItexmoSms;

// ...

public function sendMessage()
{
    $result = ItexmoSms::send('1234567890', 'Your message here');

    if ($result->isSuccess()) {
        // SMS sent successfully
    } else {
        // Handle error
        $errorMessage = $result->getErrorMessage();
    }
}
```

## Error Handling

The package handles various API responses and errors as per Itexmo's documentation. When sending an SMS, you can check for success and retrieve error messages:

```php
$result = ItexmoSms::send($phoneNumber, $message);

if (!$result->isSuccess()) {
    $errorCode = $result->getErrorCode();
    $errorMessage = $result->getErrorMessage();
    
    // Log or handle the error as needed
    Log::error("SMS sending failed. Error code: $errorCode, Message: $errorMessage");
}
```

## Testing

This package comes with a set of PHPUnit tests. To run the tests, use:

```bash
vendor/bin/phpunit
```

The tests cover:
- Successful SMS sending
- Error handling for failed SMS sending
- Configuration loading
- API response parsing

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
