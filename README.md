#  Web Application Firewall :zap: `PHP`

Shieldon is a Web Application Firewall (WAF) for PHP, with a beautiful and useful control panel that helps you easily manage the firewall rules and security settings.

![Shieldon - Web Application Firewall for PHP](https://i.imgur.com/G4xpugB.png)

![build](https://github.com/terrylinooo/shieldon/workflows/build/badge.svg) [![codecov](https://codecov.io/gh/terrylinooo/shieldon/branch/2.x/graph/badge.svg?v=202008201)](https://codecov.io/gh/terrylinooo/shieldon) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/terrylinooo/shieldon/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/terrylinooo/shieldon/?branch=2.x) [![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)


- Website: [https://shieldon.io](https://shieldon.io/)
- Wiki: [https://github.com/terrylinooo/shieldon/wiki](https://github.com/terrylinooo/shieldon/wiki)
- GitHub repository:  [https://github.com/terrylinooo/shieldon](https://github.com/terrylinooo/shieldon)
- WordPress plugin: [https://wordpress.org/plugins/wp-shieldon/](https://wordpress.org/plugins/wp-shieldon/)

## Demo

- Before you installing Shieldon, you can try the online [DEMO](https://shieldon.io/demo/) of the firewall control panel, the user and password both are `demo`.
- Try temporarily blocked by Shieldon, refreshing serveral times on [shieldon.io](https://shieldon.io/), then you will see a dialog asking you for solving Google ReCaptcha to get unblocked.

## Installation

Install via PHP Composer.
```php
composer require shieldon/shieldon ^2
```

This will also install dependencies built for Shieldon:

| package | description |
| --- | --- |
| [shieldon/psr-http](https://github.com/terrylinooo/psr-http) | PSR-7, 15, 17 Implementation with full documented and well tested. |
| [shieldon/event-dispatcher](https://github.com/terrylinooo/event-dispatcher) | Simple event dispatcher. |
| [shieldon/web-security](https://github.com/terrylinooo/web-security) | Collection of functions about web security. |
| [shieldon/messenger](https://github.com/terrylinooo/messenger) |  Collection of modules of sending message to third-party API or service, such as Telegram, Line, RocketChat, Slack, SendGrid, MailGun and more... |

## Concepts

This is basic concepts about how Shieldon works.

![](https://i.imgur.com/pRbI7gg.png)

- The network-layer firewall such as CloudFlare.
- The system-layer firewall such as iptables module.
- To use firewall software in the Web application layer, you can implement Shieldon in a very early stage of your APP, mostly just after Composer autoloader, or at the first place of middleware-pipeline.
- Shieldon analyzes all your HTTP and HTTPS requests.
- Once Shieldon has detected strange behaviors of a request, blocking and prompting them CAPTCHA to unblock.
![Firewall Dialog 1](https://i.imgur.com/rlsEwSG.png)
- If a request fails in a row many times (depends on your setting), they will be permanently banned in current data circle.
![Firewall Dialog 2](https://i.imgur.com/Qy1sADw.png)
- If a request has been permanently banned, but they still access your page, drop them in System-layer firewall - iptables. (You have to set up iptables bridge correctly)

## How to Use

### Integration with Frameworks

There are some step-by-step installation guides that lead you implementing Shieldon firewall on your PHP application. Choose a framework you are using.

|   |   |   |   |
| --- | --- | --- | --- |
| ![Firewall in Laravel](https://shieldon.io/images/home/laravel-framework-firewall.png) | ![Firewall in CakePHP](https://shieldon.io/images/home/cakephp-framework-firewall.png) | ![Firewall in Symfony](https://shieldon.io/images/home/symfony-framework-firewall.png) | ![Firewall in PHPixie](https://shieldon.io/images/home/phpixie-framework-firewall.png) |
| [Laravel](https://github.com/terrylinooo/shieldon/wiki/Laravel-Framework) | [CakePHP 3](https://github.com/terrylinooo/shieldon/wiki/CakePHP-Framework) | [Symfony](https://github.com/terrylinooo/shieldon/wiki/Symfony-Framework)  | [PHPixie](https://github.com/terrylinooo/shieldon/wiki/PHPixie-Framework) |
| ![Firewall in FatFree](https://shieldon.io/images/home/fatfree-framework-firewall.png) | ![Firewall in CodeIgniterr](https://shieldon.io/images/home/codeigniter-framework-firewall.png) | ![Firewall in Yii Framework](https://shieldon.io/images/home/yii-framework-firewall.png) | ![Firewall in Zend](https://shieldon.io/images/home/zend-framework-firewall.png) |
| [FatFree](https://github.com/terrylinooo/shieldon/wiki/FatFree-Framework) | [CodeIgniter 3](https://github.com/terrylinooo/shieldon/wiki/CodeIgniter-3-Framework)<br />[CodeIgniter 4](https://github.com/terrylinooo/shieldon/wiki/CodeIgniter-4-Framework) | [Yii 2](https://github.com/terrylinooo/shieldon/wiki/Yii-2-Framework) | [Zend MVC](https://github.com/terrylinooo/shieldon/wiki/Zend-Framework-MVC)<br />[Zend Expressive](https://github.com/terrylinooo/shieldon/wiki/Zend-Framework-Expressive) |
| ![Firewall in Slim](https://shieldon.io/images/home/slim-framework-firewall.png) | ![Firewall in Fuel](https://shieldon.io/images/home/fuel-framework-firewall.png) | - |
| [Slim 3](https://github.com/terrylinooo/shieldon/wiki/Slim-3-Framework)<br />[Slim 4](https://github.com/terrylinooo/shieldon/wiki/Slim-3-Framework) | [Fuel](https://github.com/terrylinooo/shieldon/wiki/Fuel-Framework) |  [Pure PHP project](https://github.com/terrylinooo/shieldon/wiki/Pure-PHP-Project) |

Listed frameworks: [Laravel](https://shieldon.io/en/guide/laravel.html), [Symfony](https://shieldon.io/en/guide/symfony.html), [CodeIgniter](https://shieldon.io/en/guide/codeigniter.html), [CakePHP](https://shieldon.io/en/guide/cakephp.html), [Yii](https://shieldon.io/en/guide/yii.html), [Zend](https://shieldon.io/en/guide/zend.html), [Slim](https://shieldon.io/en/guide/slim.html), [Fat-Free](https://shieldon.io/en/guide/fatfree.html), [Fuel](https://shieldon.io/en/guide/fuel.html), [PHPixie](https://shieldon.io/en/guide/phpixie.html). Can't find the documentation of the framework you are using?

There are three ways you can choose to use Shieldon on your application.

- Implement Shieldon as a *`PSR-15 middleware`*.
- Implement Shieldon in the *`bootstrap stage`* of your application.
- Implement Shieldon in the *`parent controller`* extended by the other controllers.

Shieldon `2.x` implements PSR-7 so that it could be compatible with modern frameworks such as Laravel, Symfony, Slim, Yii and so on.

### PSR-15 Middleware

#### `Example: Slim 4 framework`

In this example, I will give you some tips on how to implement Shieldon as a PSR-15 middleware.

I use Slim 4 framwork for demonstration. This way can be used on any framework supporting PSR-15 too, just with a bit modification.

#### (1) Create a firewall middleware.

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

        // The base url for the control panel.
        $firewall->controlPanel('/firewall/panel/');

        $response = $firewall->run();

        if ($response->getStatusCode() !== 200) {
            $httpResolver = new \Shieldon\Firewall\HttpResolver();
            $httpResolver($response);
        }

        return $response;
    }
}
```

#### (2) Add the firewall middleware in your application.

For example, if you are using Slim 4 framework, the code should look like this.

```php
$app->add(new FirewallMiddleware());
```

#### (3) Create a route for control panel.

For example, if you are using Slim 4 framework, the code should look like this. Then you can access the URL `https://yourwebsite.com/firewall/panel` to login to control panel.

```php
$app->any('/firewall/panel[/{params:.*}]', function (Request $request, Response $response, $args) {
    $firewall = new \Shieldon\Firewall\Firewall($request, $response);

    // The directory in where Shieldon Firewall will place its files.
    // Must be the same as firewallMiddleware.
    $firewall->configure(__DIR__ . '/../cache/shieldon_firewall');

    $panel = new \Shieldon\Firewall\Panel();
    $panel->entry();
});
```

Note:
- The HTTP method `POST` and `GET` both should be applied to your website.
- `POST` method is needed for solving CAPTCHA by users who were temporarily blocked.

### Bootstrap Stage

#### `Example: Laravel 6 framework`

Initialize Shieldon in the bootstrap stage of your application, mostly in just right after composer autoloader has been included.

In this example, I use Laravel 6 for demonstration.

#### (1) Before Initializing the $app

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

    // This directory must be writable.
    // We put it in the `storage/shieldon_firewall` directory.
    $storage =  __DIR__ . '/../storage/shieldon_firewall';

    $firewall = new \Shieldon\Firewall\Firewall();
    $firewall->configure($storage);

    // The base url for the control panel.
    $firewall->controlPanel('/firewall/panel/');
    $response = $firewall->run();

    if ($response->getStatusCode() !== 200) {
        $httpResolver = new \Shieldon\Firewall\HttpResolver();
        $httpResolver($response);
    }
}
```

#### (2) Define a route for firewall panel.

```php
Route::any('/firewall/panel/{path?}', function() {

    $panel = new \Shieldon\Firewall\Panel();
    $panel->csrf(['_token' => csrf_token()]);
    $panel->entry();

})->where('path', '(.*)');
```

### Parent Controller

#### `Example: CodeIgniter 3 framework`

If you are using a MVC framework, implementing Shieldon in a parent controller is also a good idea. In this example, I use CodeIgniter 3 for demonstration.

#### 1. Create a parent controller.

Let's create a `MY_Controller.php` in the `core` folder.

```php
class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
}
```

#### 2.  Initialize Firewall instance

Put the initial code in the constructor so that any controller extends `MY_Controller` will have Shieldon Firewall initialized and `$this->firewall()` method ready.

```php
class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Composer autoloader
        require_once APPPATH . '../vendor/autoload.php';

        // This directory must be writable.
        $storage = APPPATH . 'cache/shieldon_firewall';

        $firewall = new \Shieldon\Firewall\Firewall();
        $firewall->configure($storage);

        // The base url for the control panel.
        $firewall->controlPanel('/firewall/panel/');
        $response = $firewall->run();

        if ($response->getStatusCode() !== 200) {
            $httpResolver = new \Shieldon\Firewall\HttpResolver();
            $httpResolver($response);
        }
    }

    /**
     * Shieldon Firewall protection.
     */
    public function firewall()
    {
        $firewall = \Shieldon\Container::get('firewall');
        $firewall->run();
    }
}
```

#### 3.  Defind a controller for controll panel.

We need a controller to get into Shieldon firewall controll panel, in this example, we defind a controller named `Firewall`.

```php
class Firewall extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * This is the entry of our Firewall Panel.
     */
    public function panel()
    {
        $panel = new \Shieldon\Firewall\Panel();
        $panel->entry();
    }
}
```

Finally, no matter which way you choose, entering `https://yoursite.com/firewall/panel/`, the login page is suppose to be shown on your screen.

![](https://i.imgur.com/GFKzNYh.png)

The default user and password is `shieldon_user` and `shieldon_pass`. The first thing to do is to change the login and password after you login to control panel.

![Firewall Panel](https://i.imgur.com/MELx6Vl.png)


##  Contributing

Thank you for your interest in contributing to our project! We welcome contributions from everyone. Before getting started, please take a moment to review the guidelines below:

### Guidelines

- Fork the repository and create your branch from master.
- Make sure your code follows our coding style and conventions.
- Keep your code concise, well-documented, and modular.
- Write clear commit messages that describe the purpose of your changes.
- Test your changes thoroughly to ensure they don't introduce any new issues.
- Make sure your code builds successfully without any errors or warnings.
- Update relevant documentation, including README files if necessary.
- Submit a pull request (PR) to the master branch of the original repository.

### Code Testing

We utilize a Docker image that includes various dependencies for our code testing. The image is based on `/tests/Fixture/docker/Dockerfile`.

Follow the steps below to run the tests:

- Make sure you have Docker installed on your machine. If not, you can download and install it from the official Docker website.
- Navigate to the project directory and build the Docker image by running the following command:
    ```
    composer test:docker:build
    ```
- Once the Docker image is built, you can run the tests by executing the following command:
    ```
    composer test:docker:run
    ```
- Observe the test results and make note of any failures or errors. The output will be displayed in the terminal.

The coverage report will be generated in the `/tests/report` directory. You can view the report by opening the `index.html` file in your browser.

---

## Author

Shieldon library is brought to you by [Terry L.](https://terryl.in) from Taiwan.

## License

Shieldon Firewall is an open-sourced software licensed under the **MIT** license.
