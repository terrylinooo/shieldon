# Shieldon

![](https://i.imgur.com/G4xpugB.png)

[![Build Status](https://travis-ci.org/terrylinooo/shieldon.svg?branch=master)](https://travis-ci.org/terrylinooo/shieldon) [![codecov](https://img.shields.io/codecov/c/github/terrylinooo/shieldon.svg)](https://codecov.io/gh/terrylinooo/shieldon) ![PHP from Packagist](https://img.shields.io/packagist/php-v/terrylinooo/shieldon.svg) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Shieldon is a Web Application Firewall (WAF) for PHP community. 

- Document: https://shield-on-php.github.io (v2)
- V3 document site is coming soon.
- **Version: 3.0.0**

## Features

- SEO friendly.
- Basic Http-type DDOS protection.
- Anti-scraping.
- Online session control.
- Cross-site scripting (XSS) protection.
- Interrupting vulnerability scanning.
- Eradicating brute force attacks.
- IP manager.
- Protecting pages via WWW-Authenticate.
- Detailed statistics and charts.
- ?

## Install

Use PHP Composer:

```php
composer require terrylinooo/shieldon
```

Or, download it and include the Shieldon autoloader.
```php
require 'Shieldon/src/autoload.php';
```

Implementing Shieldon Firewall on your Web Application is pretty easy by using Firewall Panel, and I highly recommend you choose this way.


## Laravel 5, 6

For Laravel lovers, you can choose Middleware or Bootstrap to implement Shieldon Firewall on your Web application. I prefer Bootstrap personally.

### Middleware

#### (1) Define a Middleware.

Define a middleware named `ShieldonFirewall`
```
php artisan make:middleware ShieldonFirewall
```
Add several lines in the `ShieldonFirewall` middleware class:

```php
$firewall = new \Shieldon\Firewall(storage_path('shieldon'));

// Pass Laravel CSRF Token to Captcha form.
$firewall->shieldon->setCaptcha(new \Shieldon\Captcha\Csrf([
    'key' => '_token',
    'value' => csrf_token(),
]));

$firewall->restful();
$firewall->run();
```

#### (2) Register a Middleware alias.

Modify `app/Http/Kernel.php` and add this line in `$routeMiddleware` property.
```php
'firewall' => \App\Http\Middleware\ShieldonFirewall::class,
```

#### (3) Defind a Route for Firewall Panel.

We need a controller to get into Shieldon firewall controll panel, so that..

```php
Route::any('/your/secret/place/', function() {
    $firewall = \Shieldon\Container::get('firewall');
    $controlPanel = new \Shieldon\FirewallPanel($firewall);
    $controlPanel->csrf('_token', csrf_token());
    $controlPanel->entry();
})->middleware('firewall');
```

Add `firewall` middleware to any route you would like to protect. For example:

```php
Route::get('/', function () {
    return view('welcome');
})->middleware('firewall');
```

### Bootstrap

#### (1) Before Initializing $app
In your `bootstrap/app.php`, after `<?php`, add the following code.
```php
/*
|--------------------------------------------------------------------------
| Run The Shieldon Firewall
|--------------------------------------------------------------------------
|
| Shieldon Firewall will watch all HTTP requests coming to your website.
| Running Shieldon Firewall before initializing Laravel will avoid possible
| conflicts with Laravel's built-in functions.
*/

if (isset($_SERVER['REQUEST_URI'])) {

    // Notice that this directory must be writable.
    $firewallstorage = __DIR__ . '/../storage/shieldon';

    $firewall = new \Shieldon\Firewall($firewallstorage);
    $firewall->restful();
    $firewall->run();
    
}
```

#### (2) Define a Route for Firewall Panel.

```php
Route::any('/your/secret/place/', function() {
    $firewall = \Shieldon\Container::get('firewall');
    $controlPanel = new \Shieldon\FirewallPanel($firewall);
    $controlPanel->csrf('_token', csrf_token());
    $controlPanel->entry();
});
```

If you adopt this way, Shieldon Firewall will run in Global scope. But no worry, you can set up the exclusion list for the URLs you want Shieldon Firewall ignore them.

### Other Frameworks

If you're not using Laravel, no worry, Shieldon is created for lazy developers like me. Implementing Shieldon on other framework is as easy as well.

```php
// Notice that this directory must be writable.
$writableDirectory = APPPATH . 'cache/shieldon_firewall';

// Initialize Fireall instane.
$firewall = new \Shieldon\Firewall($writableDirectory);

// Get Firewall instance from Shieldon Container.
// $firewall = \Shieldon\Container::get('firewall');

// After setting up all settings nicely in Firewall Panel, 
// Shieldon will start watching all requests come to your Web Application.
$firewall->run();
```
Place this code section in a beginning section of your project.
The beginning section might be the `index.php`<sub>(1)</sub>, `Middleware` or `Parent Controller`.

<sup>(1)</sup> index.php is the entry point for all requests entering your application in most frameworks such as Laravel, CodeIgniter, Slim, WordPress and more.


```php
// Get Firewall instance from Shieldon Container.
$firewall = \Shieldon\Container::get('firewall');

// Get into the Firewall Panel.
$controlPanel = new \Shieldon\FirewallPanel($firewall);
$controlPanel->entry();
```

Put the code on the Controller and the URL that only you know.
Although it has a basic login protection.

---

## Self-built

If you would like to customize your own WAF, try the following steps and checkout document for public APIs you can use.

Here is a full example to let you know how Shieldon works and then you can manually implement Shieldon on your Web Application.

```php
$shieldon = new \Shieldon\Shieldon();

// Use SQLite as the data driver.
$dbLocation = APPPATH . 'cache/shieldon.sqlite3';
$pdoInstance = new \PDO('sqlite:' . $dbLocation);
$shieldon->setDriver(new \Shieldon\Driver\SqliteDriver($pdoInstance));

// Set components.
// This component will only allow popular search engine.
// Other bots will go into the checking process.
$shieldon->setComponent(new \Shieldon\Component\TrustedBot());

// You can ignore this setting if you only use one Shieldon on your web application. This is for multiple instances.
$shieldon->setChannel('web_project');

// Only allow 10 sessions to view current page.
// The default expire time is 300 seconds.
$shieldon->limitSession(10);

// Set a Captcha servie. For example: Google recaptcha.
$shieldon->setCaptcha(new \Shieldon\Captcha\Recaptcha([
    'key' => '6LfkOaUUAAAAAH-AlTz3hRQ25SK8kZKb2hDRSwz9',
    'secret' => '6LfkOaUUAAAAAJddZ6k-1j4hZC1rOqYZ9gLm0WQh',
]));

// Start protecting your website!

$result = $shieldon->run();


if ($result !== $shieldon::RESPONSE_ALLOW) {
    if ($shieldon->captchaResponse()) {

        // Unban current session.
        $shieldon->unban();
    }
    // Output the result page with HTTP status code 200.
    $shieldon->output(200);
}

```

##  Screenshots


### Firewall Panel

The default login username and password are `wp_shieldon_user` and `wp_shieldon_pass`.
You have set up the settings to make Shieldon work.

![](https://i.imgur.com/rkqR5mv.png)

### Temporarily Ban a User

When the users or robots are trying to view many your web pages in a short period of time, they will temporarily get banned. Get unbanned by solving a Catpcha.

![](https://i.imgur.com/FfG8fTF.png)


### Permanently Ban a User

When an user has been permanently banned.

![](https://i.imgur.com/7PdjkKV.png)


### Online Session Control

When an user has reached the online session limit. You can set the online session limit by using `limitSession` API.
![](https://i.imgur.com/1HpMO5Q.png)

## License

MIT

## Author

Shieldon library is brought to you by [Terry L.](https://terryl.in) from Taiwan.

Do you own a WordPress website?

I have made a WordPress plugin called [WP Shieldon](https://wordpress.org/plugins/wp-shieldon), it is based on Shieldon library. You can check out the [source code](https://github.com/terrylinooo/wp-shieldon) to understand about how to implement Shieldon in your PHP project.
