<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';

use League\OAuth2\Client\Provider\Google;

function getGoogleProvider() {
    $redirectUri = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
                   "://$_SERVER[HTTP_HOST]/oauth_callback.php";
    
    return new Google([
        'clientId'     => getenv('GOOGLE_CLIENT_ID') ?: '',
        'clientSecret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
        'redirectUri'  => $redirectUri,
    ]);
}

function isGoogleOAuthConfigured() {
    return !empty(getenv('GOOGLE_CLIENT_ID')) && !empty(getenv('GOOGLE_CLIENT_SECRET'));
}
