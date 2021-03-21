<?php
require_once('settings.php');

$href = 'https://www.patreon.com/oauth2/authorize' . 
    '?response_type=code' .
    '&client_id=' . PATREON_CLIENT_ID .
    '&redirect_uri=' . urlencode(PATREON_REDIRECT_URI) .
    '&scope=' . PATREON_SCOPE;

header("Location: $href");
exit();
