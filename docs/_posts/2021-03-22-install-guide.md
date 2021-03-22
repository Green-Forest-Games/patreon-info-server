---
layout: post
title:  "Install Guide for Patreon Info"
date:   2021-03-22 20:00:00 +0100
categories: patreon-info
---
# Introduction
Patreon Info is a code plugin for Unreal Engine 4 which allows you to obtain Patreon information from your user, such as their profile and pledge info. This allows you to:

* Display patron user information in-game.
* Grant access to exclusive features or bonuses to supporters.
* And much more!

**IMPORTANT:** Before purchasing the plugin, make sure you read the prerequisites section below!

# Prerequisites
In order to use this plugin, you **must** have a web server that can execute PHP code. This server will be running code that handles communications between your game/project and the Patreon servers.
The server code is available free of charge, you are free to set that up before buying the Unreal Engine plugin.
Check the engine compatibility on the store page before buying the plugin.
An example project is provided below, but you should set up your server below first.

# Server set-up
This section will run you through the installation and set-up for the server code. If you get stuck during the set-up process, feel free to send us an e-mail at the support e-mail listed in the Unreal Engine marketplace store page.

## Step 1: Download the server code
[Download from GitHub][github-server-releases]

## Step 2: Deploying the server code
1. Upload the contents of the server code zip file to your server's **public** folder using a FTP application.
  * Common folder names for this are `/httpdocs` or `/var/www`.
2. Test if you can access the files via your browser.
  * For example **"www.yourwebsite.com/get-token.php"**
  * If you do **not** get a HTTP 404 error, you're good!
  * You will probably see another error message, that is normal.

## Step 3: Configuring the server
1. [Follow these steps][patreon-register-client] to register a client on Patreon, pay attention to the following settings when creating a client:
  * `Redirect URIs`: Make sure to fill in the URI to the process-patreon.php file (i.e. `http://www.yourwebsite.com/process-patreon.php`).
  * `Client API Version`: Must be 2.
2. In the server code files, open the `settings.php` file and fill in the following settings:
  * `PATREON_CLIENT_ID`: replace `your_patreon_client_id_here` with the client ID you have received from the Patreon website.
  * `PATREON_CLIENT_SECRET`: The secret you have received from the Patreon website. Make sure this does **not** leak out!
  * `PATREON_SCOPE`: Keep this unchanged.
  * `PATREON_REDIRECT_URI`: Change this to the exact same URL you filled in the `Redirect URIs` field on the patreon website.
  * `PATREON_WHITELIST`: Add user IDs of specific patrons here to give them special treatment in your game, it will just set a boolean in the game data structure and not change anything else.
  * `PATREON_BLACKLIST`: Add user IDs of specific patrons here to give them special treatment in your game, it will just set a boolean in the game data structure and not change anything else.
  * `JWT_KEY`: This is a signing key for the data the server creates. Change this to a safe password.
  * `JWT_ISSUER` (optional): Change this to your company or personal name.
  * `JWT_AUDIENCE` (optional): Change this to how you call your audience (e.g. "My Game Players").
  * `JWT_NOT_BEFORE` (optional): Change this to the time tokens will become valid (useful for making your Patreon service become active at a later point).
  * `JWT_EXPIRES_IN`: Change this to how long a token stays valid (default: 30 days).
  * `AES_PASSWORD`: This is a password used for encrypting and decrypting the patreon access tokens. Change this to a safe password.
  * `VALIDATION_ACCEPTS_USER_AGENT`: Change this to how you want your game to identify itself as.
3. Save the settings file and upload it to your website, overwrite the settings file if it was already there.
4. Test the `get-token.php` file again via your browser.
  * You should now see a Patreon permission screen.
  * If you accept, you should see a simple success message.

# Example project
Now that you have set up your server, you can go ahead and see how the plugin works in a provided example project.

1. Purchase and install the plugin to your engine if you haven't already done so.
2. [Download the example project][example-project] (UE4.26, compatible with Patreon Info plugin v0.9)
3. Open the project and make sure the plugin is enabled in the Plugins window.
4. Go to your Project Settings, go to `Game > Patreon Info` and fill in the following settings:
  * `Get Token URL`: Change this to the `get-token.php` URL (i.e. `www.yourwebsite.com/get-token.php`).
  * `Token Obtained URL`: Change this to the `process-patreon.php` URL (i.e. `www.yourwebsite.com/process-patreon.php`).
  * `Patreon Info Get URL`: Change this to the `get-patreon-info.php` URL (i.e. `www.yourwebsite.com/get-patreon-info.php`).
  * `Patreon Campaign ID`: Change this to your Patreon campaign ID ([How to obtain your campaign ID][patreon-campaign-id])
  * `User Agent Name`: Change this to the same you filled in at `VALIDATION_ACCEPTS_USER_AGENT` in `settings.php`.
  * `Browser Class`: Change this to `ExampleBrowserWidget` (or make your own UMG widget).
  * If you make your own UMG widget, make sure to reparent your UMG blueprint from `Patreon Info Browser Widget` after creating it.
  * `Browser Widget ZOrder`: Change this if the browser widget is being hidden by other UI.
5. Open the `ExampleMap`, hit `Play` and interact with the `UI`.
6. Open the `ExampleActor` for an example blueprint implementation.

# How it works
The plugin works in two main steps: Step 1 is to obtain a Patreon token (used for getting the actual information later), the user must give permission via a browser in order to generate the token. This plugin used the built-in web browser functionality to spawn a browser inside your Unreal project for an optimal user experience. Once step 1 completes successfully, the token is returned to you and should be saved to disk or stored in a variable. If you later load the token from disk, step 1 can be skipped entirely until the token expires.

Step 2 takes the token and sends it to the server. The server will then use this token to talk with the Patreon API and return user information back to your UE4 game/project.

# Limitations
* If the user has logged in for the first time with 2-factor authentication enabled, the user needs to verify via e-mail. The verification link in the e-mail might open a different browser. In order to continue in-game, the built-in browser should be closed and log-in procedure should be retried (which will succeed after verification).
* Logging into Patreon with Google or Facebook does not work in the built-in browser widget. Log in using your e-mail instead.

# Questions
If you have any questions, suggestions or concerns, feel free to reach out to us via the support e-mail listed in the Unreal Engine marketplace page or the Support button in the Plugins window.

[github-server-releases]: https://github.com/Green-Forest-Games/patreon-info-server/releases
[example-project]: {{site.url}}/docs/files/PatreonInfoExample_5.zip
[patreon-register-client]: https://docs.patreon.com/#clients-and-api-keys
[patreon-campaign-id]: https://www.patreondevelopers.com/t/campaign-id-place/68