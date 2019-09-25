# Shieldon

![](https://i.imgur.com/G4xpugB.png)

[![Build Status](https://travis-ci.org/terrylinooo/shieldon.svg?branch=master)](https://travis-ci.org/terrylinooo/shieldon) [![codecov](https://img.shields.io/codecov/c/github/terrylinooo/shieldon.svg)](https://codecov.io/gh/terrylinooo/shieldon) ![PHP from Packagist](https://img.shields.io/packagist/php-v/terrylinooo/shieldon.svg) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Shieldon is a Web Application Firewall (WAF) for PHP community. 

- Website: https://shieldon.io
- Document: https://shield-on-php.github.io
- Version: 3.0.0

## Features

- SEO friendly.
- Basic Http-type DDOS protection.
- Anti-scraping.
- Cross-site scripting (XSS) protection.
- Online session control.
- Interrupting vulnerability scanning.
- Eradicating brute force attacks.
- IP manager.
- Protecting pages via WWW-Authenticate.
- Detailed statistics and charts.

## Install

Use PHP Composer:
```php
composer require terrylinooo/shieldon
```
Or, download it and include the Shieldon autoloader.
```php
require 'Shieldon/src/autoload.php';
```

## How to use

---

### Managed by Firewall Panel

Implementing Shieldon Firewall on your Web Application is pretty easy by using Firewall Panel, and I highly recommend you choose this way.

#### Step 1

Place this code section in a beginning section of your project.
The beginning section might be the `index.php`<sub>(1)</sub>, `Middleware` or `Parent Controller`.

<sup>(1)</sup> index.php is the entry point for all requests entering your application in most frameworks such as Laravel, CodeIgniter, Slim, WordPress and more.

```php
// Notice that this directory must be writable.
$writableDirectory = APPPATH . 'cache/shieldon_firewall';

// Initialize Fireall instane.
$firewall = new \Shieldon\Firewall($writableDirectory);
```

#### Step 2

```php
// Get Firewall instance from Shieldon Container.
$firewall = \Shieldon\Container::get('firewall');

// After setting up all settings nicely in Firewall Panel, 
// Shieldon will start watching all requests come to your Web Application.
$firewall->run();
```

#### Step 3

Put the code on the Controller and the URL that only you know.
Although it has a basic login protection.

```php
// Get Firewall instance from Shieldon Container.
$firewall = \Shieldon\Container::get('firewall');

// Get into the Firewall Panel.
$controlPanel = new \Shieldon\FirewallPanel($firewall);
$controlPanel->entry();
```

![](https://i.imgur.com/rkqR5mv.png)

The default login username and password are `wp_shieldon_user` and `wp_shieldon_pass`.
You have set up the settings to make Shieldon work.

---
### Managed by yourself

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

## Screenshot

When the users or robots are trying to view many your web pages in a short period of time, they will temporarily get banned. Get unbanned by solving a Catpcha.

![](https://i.imgur.com/FfG8fTF.png)

When an user has reached the online session limit. You can set the online session limit by using `limitSession` API.
![](https://i.imgur.com/1HpMO5Q.png)

When an user has been permanently banned.

![](https://i.imgur.com/7PdjkKV.png)

I have made a WordPress plugin called [WP Shieldon](https://wordpress.org/plugins/wp-shieldon), it is based on Shieldon library. You can check out the [source code](https://github.com/terrylinooo/wp-shieldon) to understand about how to implement Shieldon in your PHP project.

## License

MIT

## Author

Shieldon library is brought to you by [Terry L.](https://terryl.in) from Taiwan.
