<?php
require_once('functions.php');
require_once('settings.php');

require_once("vendor/patreon/patreon/src/OAuth.php");

require_once("vendor/firebase/php-jwt/src/JWT.php");

use Patreon\OAuth;
use \Firebase\JWT\JWT;

if (!empty($_GET['token'])) {
    ShowMessageAndExit('Token obtained, waiting for application to continue...');
}

// Validate if user gave permission.
if (empty($_GET['code'])) {
    ShowMessageAndExit('To link your Patreon, permission is required.');
}

// Obtain OAuth information.
$oauth = new OAuth(PATREON_CLIENT_ID, PATREON_CLIENT_SECRET);
$tokens = $oauth->get_tokens($_GET['code'], PATREON_REDIRECT_URI);

if (isset($tokens['error'])) {
    ShowMessageAndExit('Patreon token get failed: ' . $tokens['error']);
}

// Encrypt data
$decrypted_data = implode(",", array(
    "access_token" => $tokens['access_token'],
    "expires_at" => date("Y-m-d H:i:s", time() + $tokens['expires_in']),
    "refresh_token" => $tokens['refresh_token'],
    "ip" => $_SERVER['REMOTE_ADDR']
));
$encrypted_data = Encrypt($decrypted_data);

// Encode JWT
$decoded_jwt = array(
    "iss" => JWT_ISSUER,
    "aud" => JWT_AUDIENCE,
    "iat" => time(),
    "exp" => time() + JWT_EXPIRES_IN,
    "nbf" => JWT_NOT_BEFORE,
    "data" => array(
        "encrypted" => base64_encode($encrypted_data)
    )
);
$encoded_jwt = JWT::encode($decoded_jwt, JWT_KEY);

// Return JWT token
$href = PATREON_REDIRECT_URI . '?token=' . $encoded_jwt;
header("Location: $href");
exit();
