# Shieldon

![](https://i.imgur.com/G4xpugB.png)

[![Build Status](https://travis-ci.org/terrylinooo/shieldon.svg?branch=master)](https://travis-ci.org/terrylinooo/shieldon)

Shieldon, a PHP library that provides anti-scraping, XSS filitering and traffic controll for your web application. As if you are using a shield on your web applicaion to fight against bad-behavior bots, crawlers or vulnerability scanning and so on.

- Document: https://shield-on-php.github.io

- Shieldon requires at least `PHP 7.1` to run.

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

Here is a full example let you know how Shieldon works.

```php
$shieldon = new \Shieldon\Shieldon();

// Use SQLite as the data driver.
$dbLocation = APPPATH . 'cache/shieldon.sqlite3';
$pdoInstance = new \PDO('sqlite:' . $dbLocation);
$shieldon->setDriver(new \Shieldon\Driver\SqliteDriver($pdoInstance));

// Set components.
// This compoent will only allow popular search engline.
// Other bots will go into the checking process.
$shieldon->setComponent(new \Shieldon\Component\TrustedBot());

// You can ignore this setting if you only use one Shieldon on your web application. This is for multiple instances.
$shieldon->setChannel('web_project');

// Only allow 10 sessions to view current page.
// The defailt expire time is 300 seconds.
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

When the users or robots are trying to view many your web pages at a short period of time, they will temporarily get banned. To get unbanned by solving Catpcha.

![](https://i.imgur.com/FfG8fTF.png)

When an user has reached the online session limit. You can set the online session limit by using `limitSession` API.
![](https://i.imgur.com/1HpMO5Q.png)

When an user has been permanently banned.

![](https://i.imgur.com/7PdjkKV.png)

## License

MIT

## Author

- [Terry Lin](https://terryl.in)
