<?php
// PATREON SETTINGS
// Client ID for this server, obtained here: https://www.patreon.com/portal/registration/register-clients
define('PATREON_CLIENT_ID', 'your_patreon_client_id_here');
// Client secret for this server, obtained here: https://www.patreon.com/portal/registration/register-clients
define('PATREON_CLIENT_SECRET', 'your_patreon_client_secret_here');
// Comma-seperated string of permissions to get from the user, see also: https://docs.patreon.com/#scopes
define('PATREON_SCOPE', 'identity');
// URI to return from when the user has (or hasn't) given permission.
define('PATREON_REDIRECT_URI', 'https://example.com');
// List of user IDs that you can flag as whitelisted, won't actually change anything except set a separate flag in the data structure you receive in your game.
// You can obtain the user ID by going to the patron's page and checking the URL bar (should be like https://www.patreon.com/user?u=20129695, copy the number into this list).
define('PATREON_WHITELIST', array(20129695)); // To add more users, add a comma and the new value just after the last number.
// List of user IDs that you can flag as blacklisted, won't actually change anything except set a separate flag in the data structure you receive in your game.
// See above for instructions on how to obtain a user ID.
define('PATREON_BLACKLIST', array(20129695)); // To add more users, add a comma and the new value just after the last number.

// JWT SETTINGS
// Signature key used to sign the JWT.
define('JWT_KEY', 'your_jwt_secret_key_here');
// Name or URL of your company (the instance issueing the JWT key).
define('JWT_ISSUER', 'your_company_name_here');
// Name or URL of your key audience.
define('JWT_AUDIENCE', 'your_audience_name_here');
// Not valid before timestamp, in seconds since 1 Jan 1970. Use this to invalidate keys older than a specified date.
define('JWT_NOT_BEFORE', 1500000000);
// Expiry duration of a newly created key, in seconds.
define('JWT_EXPIRES_IN', 2592000); // 30 days

// AES ENCRYPTION SETTINGS
// Password used for AES encrypting and decrypting the data of the JWT.
define('AES_PASSWORD', 'your_secret_aes_password_here');

// VALIDATION SETTINGS
// Simple client validation by checking the user agent.
define('VALIDATION_ACCEPTS_USER_AGENT', 'Application/YourGameNameHere');