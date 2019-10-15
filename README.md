![Shieldon - Web Application Firewall for PHP](https://i.imgur.com/G4xpugB.png)

[![Build Status](https://travis-ci.org/terrylinooo/shieldon.svg?branch=master)](https://travis-ci.org/terrylinooo/shieldon) [![codecov](https://img.shields.io/codecov/c/github/terrylinooo/shieldon.svg)](https://codecov.io/gh/terrylinooo/shieldon) ![PHP from Packagist](https://img.shields.io/packagist/php-v/terrylinooo/shieldon.svg) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Shieldon is a Web Application Firewall (WAF) for PHP. Taking less than 10 minutes only, PHP expert developers will understand how to implement Shiedon Firewall on their Web applications. The goal of this library is to make the PHP community more secure and being extremely use-to-use.

- Website: [https://shieldon.io](https://shieldon.io/)
- GitHub Repository:  [https://github.com/terrylinooo/shieldon](https://github.com/terrylinooo/shieldon)
- **Version: 3.1.3**

## Features

- SEO friendly.
- Http-type DDOS mitigation.
- Anti-scraping.
- Online session control.
- Cross-site scripting (XSS) protection.
- Interrupting vulnerability scanning.
- Eradicating brute force attacks.
- IP manager.
- Protecting pages via WWW-Authenticate.
- Detailed statistics and charts.
- More features will come...

## Installation

Use PHP Composer:

```php
composer require terrylinooo/shieldon
```

Or, download it and include the Shieldon autoloader.
```php
require 'Shieldon/src/autoload.php';
```

## Implementing

Here are the guides of integrating with the popular PHP frameworks.

- [Laravel](https://shieldon.io/en/guide/laravel.html)
- [Symfony](https://shieldon.io/en/guide/symfony.html)
- [CodeIgniter](https://shieldon.io/en/guide/codeigniter.html)
- [CakePHP](https://shieldon.io/en/guide/cakephp.html)
- [Yii](https://shieldon.io/en/guide/yii.html)
- [Zend](https://shieldon.io/en/guide/zend.html)
- [Slim](https://shieldon.io/en/guide/slim.html)
- [Fat-Free](https://shieldon.io/en/guide/fatfree.html)
- [Fuel](https://shieldon.io/en/guide/fuel.html)
- [PHPixie](https://shieldon.io/en/guide/phpixie.html)

## Firewall Panel

Since 3.0.0, Shieldon starts providing a Firewall Instance, and it's visualization UI called Firewall Panel. By using Shieldon Firewall, you can easily implement it on your Web application.

![Firewall Panel](https://i.imgur.com/MELx6Vl.png)

Click [here](/demo/) to view demo.

- user: `demo`
- password: `demo`

## Screenshots

Only a few screenshots are listed below.

### Firewall Panel

#### Captcha Stats

![Captcha Statistics](https://i.imgur.com/tjc8mW8.png)

#### Online Session Stats

You can see the real-time data here if `Online Session Limit` is enabled.

![](https://i.imgur.com/sfssPyj.png)

#### Rule Table

You can temporarily ban a user here.

![](https://i.imgur.com/5Vg2brX.png)


### Dialog

#### Temporarily Ban a User

When the users or robots are trying to view many your web pages in a short period of time, they will temporarily get banned. Get unbanned by solving a Catpcha.

![](https://i.imgur.com/rlsEwSG.png)

#### Permanently Ban a User

When a user has been permanently banned.

![](https://i.imgur.com/Qy1sADw.png)


#### Online Session Control

When a user has reached the online session limit. You can set the online session limit by using `limitSession` API.
![](https://i.imgur.com/U02w70x.png)

## Author

Shieldon library is brought to you by [Terry L.](https://terryl.in) from Taiwan.

## License

MIT
