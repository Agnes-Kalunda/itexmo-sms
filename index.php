<?php

require 'vendor/autoload.php';

use Agnes\ItexmoSms\ItexmoSms;
use Illuminate\Container\Container;

// Create a new container instance
$container = new Container();

// Register the ItexmoSms class with the container
$container->singleton(ItexmoSms::class, function () {
    $config = include 'config/itexmo.php';
    return new ItexmoSms($config);
});

// Resolve the ItexmoSms instance from the container
$itexmoSms = $container->make(ItexmoSms::class);

try {
    $response = $itexmoSms->broadcast(['recipient_number'], 'Your message here.');
    print_r($response);
} catch (\RuntimeException $e) {
    echo 'Error: ' . $e->getMessage();
}