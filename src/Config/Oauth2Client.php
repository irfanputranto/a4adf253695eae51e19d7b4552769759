<?php


namespace App\Config;

use League\OAuth2\Client\Provider\Google;

require __DIR__ . '/../../vendor/autoload.php';

$provider = new Google([
    'clientId'     => $_ENV['OAUTH2_CLIENT_ID'],
    'clientSecret' => $_ENV['OAUTH2_CLIENT_SECRET'],
    'redirectUri'  => $_ENV['OAUTH2_REDIRECT_URI'],
    'scope'        => 'profile',
    'scope'        => 'email',
]);