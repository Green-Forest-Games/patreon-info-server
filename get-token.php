<?php
require_once('functions.php');
require_once('settings.php');

$data = array(
    'response_type' => 'code',
    'client_id'     => PATREON_CLIENT_ID,
    'redirect_uri'  => PATREON_REDIRECT_URI,
    'scope'         => PATREON_SCOPE,
    'state'         => $_GET['user_id']
);
$query = http_build_query($data);
$href = 'https://www.patreon.com/oauth2/authorize?' . $query;

header("Location: $href");
exit();
