---
layout: post
title:  "Patreon Info Installation Guide"
date:   2022-09-21 18:00:00 +0200
permalink: install-guide-1_0
---
This is the installation guide for Patreon Info.

## Step 0 - Get a Web Server

In order to use Patreon Info, you must have a web server with the following capabilities:

- It must be publically accessible (via IP address or a domain).
- It must be able to run PHP.
- You must be able to upload files to it (e.g. via FTP).

A recommended approach is to pick a **website hosting provider**; these usually take care of the technical bits and offer you a place to upload your files.
If this is your first time, ask around with people that have experience with website hosting.
Free services and website builders like Weebly usually **do not work**, because you cannot upload files to your server.

## Step 1 - Set up the server

Download the latest server code from [**GitHub**](https://github.com/Green-Forest-Games/patreon-info-server/releases).
The easiest approach is to download the zip file with dependencies included, which you can find under 'Assets' for every release.
Alternatively, if you have Composer installed you can also download the Source code and use `composer install` to install the dependencies yourself.

Unzip the zip file to your computer and upload the **folder contents** to your server (the `docs` folder is not required).
Test if you can access the files via your browser (e.g. `http://yourwebsite.com/get-token.php`). You should get something like:
{% highlight json %}
{"error":"invalid_request","error_description":"Invalid client_id parameter value."}
{% endhighlight %}
This error is **expected**, it tells us your server is accessible and processing PHP correctly. Obviously we still need to configure it.

## Step 2 - Register a Patreon client

Before we configure the server, we need to register your installation of Patreon Info as a "client" with the Patreon API. For this you will need to have a Patreon creator account prepared.
To register a client, [**follow these steps**](https://docs.patreon.com/#clients-and-api-keys). Pay attention to these settings when registering the client:

- **Redirect URIs**: `http://yourwebsite.com/process-patreon.php`
  - Modify if you use **https** or have put your files in a **subdirectory**.
- **Client API Version**: Must be **2**.

Keep a note of the **Client ID** and **Client Secret** you have received after registering your client.

## Step 3 - Configure the server

We should now have all the required information to configure the server.
Open the `settings.php` file you originally downloaded to your computer with a text editor and fill in **at least** the following the settings:

- **PATREON_CLIENT_ID**: The Client ID of your Patreon client.
- **PATREON_CLIENT_SECRET**: The Client Secret of your Patreon client (keep private!).
- **PATREON_REDIRECT_URI**: Must match a URI you entered in `Redirect URIs` on Patreon.
- **JWT_KEY**: Change this to a safe password.
- **AES_PASSWORD**: Change this to a safe password.

Save the file and upload it to your website, overwrite the existing settings file if needed.
To check everything is working, open the `get-token.php` URL again in your browser.
You should get redirected to Patreon and get presented a Patreon Key after going through all the screens.

Congratulations, the server is now fully configured and ready for handling requests!

## Step 4 - Install the Unreal Engine plugin

If you haven't already, purchase the [Patreon Info plugin](https://www.unrealengine.com/marketplace/en-US/product/patreon-info) on the Unreal Marketplace.
After purchasing the plugin should become visible in the Epic Games launcher under `Unreal Engine > Library > Vault`.
Make sure to install it to your engine to start using it.

## Step 5 (Optional) - Explore the example project

Now that the plugin is installed, you can [download the example project]({{ site.url }}/patreon-info-server/files/PatreonInfoExample_6.zip) (UE4.26+) to see an implementation in action.
To get the example to work, you will need to configure it to use your server.
After opening the project and making the sure the plugin is enabled in the Plugins window, open Project Settings and change the following settings under `Game > Patreon Info`:

- **Get Token URL**: Change this to your `get-token.php` URL.
  - e.g. `www.yourwebsite.com/get-token.php`.
- **Patreon Info Get URL**: Change this to your `get-patreon-info.php` URL.
- **Patreon Campaign ID**: Change this to your [Patreon Campaign ID](https://www.patreondevelopers.com/t/campaign-id-place/68).
- **User Agent Name**: Must match what you filled in at `VALIDATION_ACCEPTS_USER_AGENT` in `settings.php`.

After filling in the settings, you should now be able to see the example in action by opening **ExampleMap** and hitting **Play**.
Also, make sure to check out the **ExampleWidget** blueprint for an example implementation.
