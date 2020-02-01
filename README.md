![Shieldon - Web Application Firewall for PHP](https://i.imgur.com/G4xpugB.png)

[![Build Status](https://travis-ci.org/terrylinooo/shieldon.svg?branch=master)](https://travis-ci.org/terrylinooo/shieldon) [![codecov](https://img.shields.io/codecov/c/github/terrylinooo/shieldon.svg)](https://codecov.io/gh/terrylinooo/shieldon) ![PHP from Packagist](https://img.shields.io/packagist/php-v/terrylinooo/shieldon.svg) [![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Shieldon is a Web Application Firewall (WAF) for PHP. Taking less than 10 minutes only, PHP expert developers will understand how to implement Shiedon Firewall on their Web applications. The goal of this library is to make the PHP community more secure and being extremely use-to-use.

- Website: [https://shieldon.io](https://shieldon.io/)
- GitHub Repository:  [https://github.com/terrylinooo/shieldon](https://github.com/terrylinooo/shieldon)

## Concepts

This is basic concepts about how Shieldon works.

![](https://i.imgur.com/pRbI7gg.png)

- The network-layer firewall such as CloudFlare.
- The system-layer firewall such as iptables module.
- To use firewall software in the Web application layer, we are capable of implementing Shieldon in a very early stage of your APP, mostly just after Composer autoloader.
- Shieldon analyzes all your HTTP and HTTPS requests.
- Once Shieldon has detected strange behaviors of a request, Shieldon will temporarily ban them and prompt them CAPTCHA for them to unban.
- If a request fails in a row many times (depends on your setting), they will be permanently banned in current data circle.
- If a request has been permanently banned, but they still access your page, drop them in System-layer firewall - iptables.

## Features

- SEO friendly
- Http-type DDOS mitigation.
- Anti-scraping.
- Online session control.
- Cross-site scripting (XSS) protection.
- Interrupting vulnerability scanning.
- Eradicating brute force attacks.
- IP manager.
- Protecting pages via WWW-Authenticate.
- Detailed statistics and charts.
- Send notifications when specific events occurred. Supported modules:
    - Telegram
    - Line Notify
    - Rocket Chat
    - Slack
    - SendGrid
    - Mailgun
    - Mail (PHP native mail)
    - SMTP
- Web UI for System firewall - iptables and ip6tables.
- More features will come...

## Installation

Use PHP Composer:

```php
composer require shieldon/shieldon
```

Or, download it and include the Shieldon autoloader.
```php
require 'Shieldon/autoload.php';
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

Shieldon provides a Firewall Instance, and it's visualization UI called Firewall Panel. By using Shieldon Firewall, you can easily implement it on your Web application.

![Firewall Panel](https://i.imgur.com/MELx6Vl.png)

Click [here](https://shieldon.io/demo/) to view demo.

- user: `demo`
- password: `demo`

## Screenshots

Only a few screenshots are listed below.

### Firewall Panel

#### Captcha Stats

![Captcha Statistics](https://i.imgur.com/tjc8mW8.png)

#### Online Session Stats

You can see the real-time data here if `Online Session Limit` is enabled.

![Firewall Panel - Online Session Control](https://i.imgur.com/sfssPyj.png)

#### Rule Table

You can temporarily ban a user here.

![Firewall Panel - Rule Table](https://i.imgur.com/5Vg2brX.png)


#### Responsive

Shieldon's Firewall Panel is fully responsive, and you can manage it when you are not in front of your computer, using your mobile phone at any time.

![Responsive Firewall Panel](https://i.imgur.com/fUz9lZD.png)

### Dialog

#### Temporarily Ban a User

When the users or robots are trying to view many your web pages in a short period of time, they will temporarily get banned. Get unbanned by solving a Catpcha.

![Firewall Dialog 1](https://i.imgur.com/rlsEwSG.png)

#### Permanently Ban a User

When a user has been permanently banned.

![Firewall Dialog 2](https://i.imgur.com/Qy1sADw.png)

#### Online Session Control

![Firewall Dialog 3](https://i.imgur.com/cAOKIY8.png)

When a user has reached the online session limit.

### Notification

Provided by [Messenger](https://github.com/terrylinooo/messenger) library.

![Telegram](https://i.imgur.com/3lqamO7.png)

Send notification via Telegram API.


## Author

Shieldon library is brought to you by [Terry L.](https://terryl.in) from Taiwan.

## Contributing

Thank you very much for considering contributing to Shieldon Firewall, yet we need your help to translate our webiste, documentation and i18n files in Shieldon library. Here are the links:

- [Website](https://github.com/shieldon-io/website-translations)
- [Documentation](https://github.com/shieldon-io/document-translations)
- [i18n files in Shieldon Library](https://github.com/shieldon-io/library-translations)

## License

Shieldon Firewall is an open-sourced software licensed under the **MIT** license.


