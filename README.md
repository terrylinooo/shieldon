# Shieldon

<p align="center">

![](https://i.imgur.com/G4xpugB.png)

[![Build Status](https://travis-ci.org/terrylinooo/shieldon.svg?branch=master)](https://travis-ci.org/terrylinooo/shieldon) [![codecov](https://img.shields.io/codecov/c/github/terrylinooo/shieldon.svg)](https://codecov.io/gh/terrylinooo/shieldon) ![PHP from Packagist](https://img.shields.io/packagist/php-v/terrylinooo/shieldon.svg) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Shieldon, a PHP library that provides anti-scraping and online session control for your web application. As if you are using a shield on your web applicaion to fight against bad-behavior bots, crawlers or vulnerability scanning and so on.

</p>

- Document: https://shield-on-php.github.io

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

Here is a full example to let you know how Shieldon works.

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
