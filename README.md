![Shieldon - Web Application Firewall for PHP](https://i.imgur.com/G4xpugB.png)

[![Build Status](https://travis-ci.org/terrylinooo/shieldon.svg?branch=2.x)](https://travis-ci.org/terrylinooo/shieldon) [![codecov](https://codecov.io/gh/terrylinooo/shieldon/branch/2.x/graph/badge.svg)](https://codecov.io/gh/terrylinooo/shieldon) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/terrylinooo/shieldon/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/terrylinooo/shieldon/?branch=2.x) [![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

## Shieldon 2 is still under development. Please don't use.

Shieldon is a Web Application Firewall (WAF) for PHP. Taking less than 10 minutes only, PHP expert developers will understand how to implement Shiedon Firewall on their Web applications. The goal of this library is to make the PHP community more secure and being extremely easy-to-use.

- Website: [https://shieldon.io](https://shieldon.io/)
- GitHub repository:  [https://github.com/terrylinooo/shieldon](https://github.com/terrylinooo/shieldon)
- WordPress plugin: [https://wordpress.org/plugins/wp-shieldon/](https://wordpress.org/plugins/wp-shieldon/)

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
    - Mail (Using Native PHP mail function.)
    - SMTP
- Web UI for System firewall - iptables and ip6tables.

## Installation

Use PHP Composer:

```php
composer require shieldon/shieldon
```

Or, download it and include the Shieldon autoloader.
```php
include 'Shieldon/autoload.php';
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

---

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

---

## Contributing

### Core Function

Welcome to contribute your idea to this project. Before sending your pull request, please make sure everything is tested well without errors.

#### Requirements

- MySQL or MariaDB installed.
- Redis installed. (Also include PHP extension `php_redis`)

#### Steps

1. Run `composer update` to install required libraries.
    ```bash
    composer update
    ```
2. Create a writable folder `tmp`. (same level with `src` folder.) for temporary testing files.
    ```bash
    mkdir tmp
    chmod 777 tmp
    ```
3. Create a MySQL database `shieldon_unittest`
    ```bash
    mysql -u root -e 'CREATE DATABASE shieldon_unittest;'
    ```
4. Create a user `shieldon'@'localhost` with password `taiwan`.
    ```bash
    mysql -u root -e "CREATE USER 'shieldon'@'localhost' IDENTIFIED BY 'taiwan';"
    ```
5. Grant database permissions on `shieldon_unittest` to `shieldon'@'localhost`.
    ```bash
    mysql -u root -e "GRANT ALL ON shieldon_unittest.* TO 'shieldon'@'localhost';"
    ```
6. Install PHP Xdebug.
    ```bash
    apt-get install php7.2-xdebug
    ```
7. Run test.
    ```bash
    composer test
    ```

### Help with Transation

Thank you very much for considering contributing to Shieldon Firewall, yet we need your help to translate our webiste, documentation and i18n files in Shieldon library. Here are the links:

- [Website](https://github.com/shieldon-io/website-translations)
- [Documentation](https://github.com/shieldon-io/document-translations)
- [i18n files in Shieldon library](https://github.com/shieldon-io/library-translations)

## Author

Shieldon library is brought to you by [Terry L.](https://terryl.in) from Taiwan.

## License

Shieldon Firewall is an open-sourced software licensed under the **MIT** license.

- [Changelog](https://github.com/terrylinooo/shieldon/wiki/Changelog)
