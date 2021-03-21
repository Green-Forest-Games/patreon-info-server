<?php
date_default_timezone_set('Europe/Amsterdam');

require_once('settings.php');

function ShowMessageAndExit($message)
{
    echo ('<!DOCTYPE html>
    <html lang="en" dir="ltr">  
        <head>
            <meta charset="utf-8">
            <title></title>
        </head>   
        <body>
            ' . $message . '  
        </body>
    </html>');
    exit();
}

function Encrypt($plaintext)
{
    $method = "AES-256-CBC";
    $key = hash('sha256', AES_PASSWORD, true);
    $iv = openssl_random_pseudo_bytes(16);

    $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
    $hash = hash_hmac('sha256', $ciphertext . $iv, $key, true);

    return $iv . $hash . $ciphertext;
}

function Decrypt($ivHashCiphertext)
{
    $method = "AES-256-CBC";
    $iv = substr($ivHashCiphertext, 0, 16);
    $hash = substr($ivHashCiphertext, 16, 32);
    $ciphertext = substr($ivHashCiphertext, 48);
    $key = hash('sha256', AES_PASSWORD, true);

    if (!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash)) return null;

    return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
}
