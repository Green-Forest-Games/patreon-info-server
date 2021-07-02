<?php
require_once('functions.php');
require_once('settings.php');

require_once("vendor/patreon/patreon/src/OAuth.php");
require_once("vendor/firebase/php-jwt/src/JWT.php");

use Patreon\OAuth;
use \Firebase\JWT\JWT;

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
    'access_token'  => $tokens['access_token'],
    'expires_at'    => date("Y-m-d H:i:s", time() + $tokens['expires_in']),
    'refresh_token' => $tokens['refresh_token'],
    'ip'            => $_SERVER['REMOTE_ADDR'],
    'user_id'       => $_GET['user_id']
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
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
    .tooltip {
        position: relative;
        display: inline-block;
    }

    .tooltip .tooltiptext {
        visibility: hidden;
        width: 140px;
        background-color: #555;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px;
        position: absolute;
        z-index: 1;
        bottom: 150%;
        left: 50%;
        margin-left: -75px;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .tooltip .tooltiptext::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #555 transparent transparent transparent;
    }

    .tooltip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }

    /* CACTUA EDITS */

    body,
    html {
        margin: 0;
        padding: 0;
        height: 100%;
        width: 100%;
    }

    input,
    textarea,
    button {
        outline: none;
    }

    body {
        background-color: #3d3535;
        font-family: Helvetica;
        box-sizing: border-box;
        padding: 60px 0px;
    }

    #container {
        width: 90%;
        max-width: 600px;
        margin: 0px auto;
        border-radius: 3px;
        background-color: #fff;
        border-top: 3px solid skyblue;
        box-sizing: border-box;
        padding: 20px;
    }

    p.title {
        text-align: center;
        font-size: 18px;
        text-transform: uppercase;
        margin: 0;
        margin-bottom: 15px;
    }

    textarea#PatreonKey {
        width: 100%;
        height: 50px;
        border: 0;
        outline: none;
        resize: none;
        padding: 20px;
        box-sizing: border-box;
        background-color: #f6f6f6;
        margin-bottom: 10px;
    }

    .tooltip {
        width: 100%;
        max-width: 200px;
        display: block;
        margin: 0 auto;
    }

    button#copy-button {
        width: 100%;
        height: 45px;
        border: 0;
        cursor: pointer;
        text-transform: uppercase;
        border-radius: 4px;
        background: #adadad;
    }
    </style>
    <title>Get Token</title>
</head>

<body>
    <div id="container">
        <p class="title"> Patreon Key: </p>
        <textarea disabled id="PatreonKey"><?php echo $encoded_jwt; ?></textarea>
        <div class="tooltip">
            <button onclick="CopyToClipboard()" onmouseout="OnMouseOut()" id="copy-button">
                <span class="tooltiptext" id="CopyButtonTooltip">Copy to clipboard</span>
                Copy key
            </button>
        </div>
    </div>

    <script>
    function CopyToClipboard() {
        var copyText = document.getElementById("PatreonKey");
        copyText.disabled = false;
        copyText.select();
        document.execCommand("copy");
        copyText.disabled = true;

        var tooltip = document.getElementById("CopyButtonTooltip");
        tooltip.innerHTML = "Patreon key copied to clipboard!";
    }

    function OnMouseOut() {
        var tooltip = document.getElementById("CopyButtonTooltip");
        tooltip.innerHTML = "Copy to clipboard";
    }

    function resize_textarea() {
        let element = document.getElementById("PatreonKey")
        element.style.height = "0px"
        element.style.height = element.scrollHeight + "px"
    }

    window.addEventListener('resize', resize_textarea)

    resize_textarea()
    </script>
</body>

</html>
