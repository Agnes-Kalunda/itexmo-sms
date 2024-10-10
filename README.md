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
