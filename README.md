![Shieldon - Web Application Firewall for PHP](https://i.imgur.com/G4xpugB.png)

[![Build Status](https://travis-ci.org/terrylinooo/shieldon.svg?branch=2.x)](https://travis-ci.org/terrylinooo/shieldon) [![codecov](https://codecov.io/gh/terrylinooo/shieldon/branch/2.x/graph/badge.svg)](https://codecov.io/gh/terrylinooo/shieldon) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/terrylinooo/shieldon/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/terrylinooo/shieldon/?branch=2.x) [![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

Shieldon is a Web Application Firewall (WAF) for PHP, with a beautiful and useful control panel that helps you easily manage the firewall rules and security settings.

- Website: [https://shieldon.io](https://shieldon.io/)
- GitHub repository:  [https://github.com/terrylinooo/shieldon](https://github.com/terrylinooo/shieldon)
- WordPress plugin: [https://wordpress.org/plugins/wp-shieldon/](https://wordpress.org/plugins/wp-shieldon/)


## Installation

### 2.x

```php
composer require shieldon/shieldon ^2
```

Shieldon `2.x` implements PSR-7 so that it could be compatible with modern frameworks such as Laravel, Symfony, Slim, Yii, etc. Using Shieldon `2.x` as a PSR-15 middleware is best practice in this case.

### 1.x

```php
composer require shieldon/shieldon ^1
```

Shieldon `1.x` directly accesses the superglobals, if you are using old frameworks (for instance, Codeigniter 3) or just pure PHP, and PSR-7 is not used, choosing this approach will be better.

## Guide

The examples here is for Shieldon 2.

### How to Use

#### Create a Firewall Middleware

```php
class FirewallMiddleware
{
    /**
     * Example middleware invokable class
     *
     * @param ServerRequest  $request PSR-7 request
     * @param RequestHandler $handler PSR-15 request handler
     *
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);

        $firewall = new \Shieldon\Firewall\Firewall($request, $response);

        // The directory in where Shieldon Firewall will place its files.
        $firewall->configure(__DIR__ . '/../cache/shieldon_firewall');
        $response = $firewall->run();

        if ($response->getStatusCode() !== 200) {
            $httpResolver = new \Shieldon\Firewall\HttpResolver();
            $httpResolver($response);
        }

        return $response;
    }
}
```

#### Add Firewall Middleware in Your Application

For example, if you are using Slim 4 framework, the code should like this.
```php
$app->add(new ExampleMiddleware());
```

#### Create a Route for Control Panel

For example, if you are using Slim 4 framework, the code should like this. Then you can access the URL `https://yourwebsite.com//firewall/panel` to login to control panel.

```php
$app->any('/firewall/panel[/{params:.*}]', function (Request $request, Response $response, $args) {
    $firewall = new \Shieldon\Firewall\Firewall($request, $response);

    // The directory in where Shieldon Firewall will place its files.
    // Must be the same as firewallMiddleware.
    $firewall->configure(__DIR__ . '/../cache/shieldon_firewall');

    $panel = new \Shieldon\Firewall\Panel();

    // The base url for the control panel.
    $panel->entry('/firewall/panel/');
});
```

The HTTP method `POST` and `GET` both should be applied to your website.


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

- SEO friendly, no impacts for SERP.
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
