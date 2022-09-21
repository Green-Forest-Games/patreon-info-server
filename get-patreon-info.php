<?php
require_once('functions.php');
require_once('settings.php');

require_once("vendor/patreon/patreon/src/API.php");
require_once("vendor/patreon/patreon/src/OAuth.php");

require_once("vendor/firebase/php-jwt/src/BeforeValidException.php");
require_once("vendor/firebase/php-jwt/src/ExpiredException.php");
require_once("vendor/firebase/php-jwt/src/SignatureInvalidException.php");
require_once("vendor/firebase/php-jwt/src/JWT.php");

use Patreon\API;
use Patreon\OAuth;
use \Firebase\JWT\JWT;

header("Content-Type: application/json; charset=UTF-8");
$returnData = array('errors' => array(), 'expired' => false, 'info' => new stdClass());
$returnData['info']->first_name = "";
$returnData['info']->last_name = "";
$returnData['info']->full_name = "";
$returnData['info']->image_url = "";
$returnData['info']->patron_status = "";
$returnData['info']->tiers = array();
$returnData['info']->userid = 0;
$returnData['info']->whitelisted = false;
$returnData['info']->blacklisted = false;
$returnData['info']->last_charge_date = "";
$returnData['info']->last_charge_status = "";
$returnData['info']->pledge_history = array();
$returnData['info']->campaign_lifetime_support_cents = 0;

function ReturnData()
{
	global $returnData;
	echo json_encode($returnData);
	exit();
}

function Fatal($error)
{
	global $returnData;
	array_push($returnData['errors'], $error);
	ReturnData();
}

// Validate input data
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
	Fatal('Invalid request method.');
}

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (strcasecmp($contentType, 'application/json') != 0) {
	Fatal('Invalid content type.');
}

$inputData = json_decode(trim(file_get_contents("php://input")));
if (json_last_error() !== JSON_ERROR_NONE) {
	Fatal('Malformed payload data.');
}

if (empty($inputData->userjwt)) {
	Fatal('Missing JWT.');
}

if (empty($inputData->campaign_id)) {
	Fatal('Missing campaign ID.');
}

if (!isset($_SERVER['HTTP_USER_AGENT']) || $_SERVER['HTTP_USER_AGENT'] != VALIDATION_ACCEPTS_USER_AGENT) {
	Fatal('Client validation failed.');
}

// Decode JWT
$encoded_jwt = $inputData->userjwt;
$decoded_jwt = null;
try {
	$returnData['expired'] = false;
	$decoded_jwt = JWT::decode($encoded_jwt, JWT_KEY, array('HS256'));
} catch (\Firebase\JWT\ExpiredException $e) {
	$returnData['expired'] = true;
	Fatal('Expired Token');
} catch (Exception $e) {
	Fatal('Access Denied: ' . $e->getMessage());
}

// Decrypt data
$encrypted_data = base64_decode($decoded_jwt->data->encrypted);
$decrypted_data = Decrypt($encrypted_data);

if ($decrypted_data === null) {
	Fatal('Decryption Error');
}

$jwt_data = explode(",", $decrypted_data);
$access_token = $jwt_data[0];
$expires_at = $jwt_data[1];
$refresh_token = $jwt_data[2];
$ip = $jwt_data[3];
$user_id = $jwt_data[4];

// Check if JWT sender is same person as creator (deals with people giving away token).
if (!empty($user_id)) {
	// v1.1 compliant clients send a unique user ID that we can use for checking.
	if ($user_id != $inputData->user_id) {
		Fatal("Authorization Error");
	}
} else {
	// Clients older than v1.1 only send an IP address.
	// This approach is error-prone for users with a VPN or Tor browser.
	if ($ip != $_SERVER['REMOTE_ADDR']) {
		Fatal("Authorization Error");
	}
}

if (time() - strtotime($expires_at) >= 0) {
	// Access token has expired, let's refresh it.
	$oauth = new OAuth(PATREON_CLIENT_ID, PATREON_CLIENT_SECRET);
	$tokens = $oauth->refresh_token($refresh_token, null);

	if (isset($tokens['error'])) {
		Fatal('Patreon access token refresh failed: ' . $tokens['error']);
	}

	$access_token = $tokens['access_token'];
	$expires_at = date("Y-m-d H:i:s", time() + $tokens['expires_in']);
	$refresh_token = $tokens['refresh_token'];
}

$api_client = new API($access_token);
$api_client->api_return_format = 'object';

$user_info = $api_client->get_data('identity?include=memberships,memberships.campaign,memberships.currently_entitled_tiers,memberships.pledge_history' .
	'&fields' . urlencode('[user]')   		. '=first_name,last_name,full_name,image_url' .
	'&fields' . urlencode('[member]') 		. '=patron_status,last_charge_date,last_charge_status,campaign_lifetime_support_cents' .
	'&fields' . urlencode('[tier]')   		. '=amount_cents,description,title,image_url' .
	'&fields' . urlencode('[pledge-event]')	. '=date,type,payment_status,tier_title');

//var_export(json_encode($user_info));

if (!property_exists($user_info, 'data')) {
	Fatal("Patreon API returned no 'data' data.");
}

$returnData['info']->first_name = $user_info->data->attributes->first_name;
$returnData['info']->last_name = $user_info->data->attributes->last_name;
$returnData['info']->full_name = $user_info->data->attributes->full_name;
$returnData['info']->image_url = $user_info->data->attributes->image_url;
$returnData['info']->userid = $user_info->data->id;

// Whitelist
foreach (PATREON_WHITELIST as $list_id) {
	if ($user_info->data->id == $list_id) {
		$returnData['info']->whitelisted = true;
		break;
	}
}

// Blacklist
foreach (PATREON_BLACKLIST as $list_id) {
	if ($user_info->data->id == $list_id) {
		$returnData['info']->blacklisted = true;
		break;
	}
}

// Patreon support status and tier.
if (property_exists($user_info, 'included')) {
	foreach ($user_info->included as $main_include) {
		if ($main_include->type == "member") {
			$test_id = $main_include->relationships->campaign->data->id;
			if ($test_id == $inputData->campaign_id) {
				$returnData['info']->patron_status = $main_include->attributes->patron_status;
				$returnData['info']->last_charge_date = $main_include->attributes->last_charge_date;
				$returnData['info']->last_charge_status = $main_include->attributes->last_charge_status;
				$returnData['info']->campaign_lifetime_support_cents = $main_include->attributes->campaign_lifetime_support_cents;

				foreach ($main_include->relationships->currently_entitled_tiers->data as $tier_meta) {
					foreach ($user_info->included as $include) {
						if ($include->type == "tier" && $include->id == $tier_meta->id) {
							$tier = new stdClass();
							$tier->amount_cents = $include->attributes->amount_cents;
							$tier->description = $include->attributes->description;
							$tier->image_url = $include->attributes->image_url;
							$tier->title = $include->attributes->title;

							array_push($returnData['info']->tiers, $tier);
						}
					}
				}

				foreach ($main_include->relationships->pledge_history->data as $pledge_meta) {
					foreach ($user_info->included as $include) {
						if ($include->type == "pledge-event" && $include->id == $pledge_meta->id) {
							array_push($returnData['info']->pledge_history, $include->attributes);
						}
					}
				}

				break;
			}
		}
	}
}

ReturnData($returnData);
