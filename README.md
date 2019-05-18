# Shieldon :shield:

Shieldon, a PHP library that prevents anti-scraping, XSS filitering and traffic controll. As if you are using a shield on your web applicaion to against bad-behavior bots, crawlers or vulnerability scanning and so on.

`version: 1.0.0.alpha`

Don't use it until first release come.

## Get started

Shieldon requires at least `PHP 7.0` to run.

### Install

```
composer require terrylinooo/shieldon
```

### How to use

Here is a simple example let you know how Shieldon works.

```php
$shieldon = new \Shieldon\Shieldon();

// Use SQLite as the data driver.
$dbLocation = APPPATH . 'cache/shieldon.sqlite3';
$pdoInstance = new \PDO('sqlite:' . $dbLocation);
$shieldon->setDriver(new \Shieldon\Driver\SqliteDriver($pdoInstance));

// Set core components.
$shieldon->setComponent(new \Shieldon\Component\Ip());
$shieldon->setComponent(new \Shieldon\Component\Robot());

// You can ignore this setting if you only use one Shieldon on your web application. This is for multiple instances.
$shieldon->setChannel('web_project');

// Start protecting your website!
$result = $shieldon->run();

if ($result) {
    echo 'You are allowed.';
} else {
    echo 'You are banned';
}
```

Other usages:
```php
// Ban an IP address 33.125.12.87 immediately.
$shieldon->banIP('33.125.12.87');

// Remove XSS string from $_GET['key'];
$shielon->xssClean('GET', 'key');
// Other examples:
$shielon->xssClean('POST', 'content');
$shielon->xssClean('COOKIE', 'tracking');

// Limit 500 sessions in 300 seconds.
$shieldon->limitTraffic(500, 300);

// Set a custom error page. It will display to the vistors who are blocked.
// Blocked user must solve CAPTCHA to continue browsering.
$shieldon->setHtml($html);
```

## Drivers

### MySQL
```
$db = [
    'host' => '127.0.0.1',
    'dbname' => 'test_projects',
    'user' => 'root',
    'pass' => 'test1234',
    'charset' => 'utf8',
];

$pdoInstance = new \PDO(
    'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'] . ';charset=' . $db['charset'],
    $db['user'],
    $db['pass']
);

$shieldon->setDriver(new \Shieldon\Driver\MysqlDriver($pdoInstance))
```

### SQLite
```
$dbLocation = APPPATH . 'cache/shieldon.sqlite3';
$pdoInstance = new \PDO('sqlite:' . $dbLocation);
$shieldon->setDriver(new \Shieldon\Driver\SqliteDriver($pdoInstance));
```

### Redis

Ongoing...
```
$redis = new \Redis();
$redis->connect('127.0.0.1', 6319);
$shieldon->setDriver(new \Shieldon\Driver\RedisDriver($redis));
```

### File

Ongoing...
```
$shieldon->setDriver(
    new \Shieldon\Driver\FileDriver([
        'directory' => APPPATH . '../cache/shieldon',
        'extension' => 'json',
    ])
);
```


## API


### banIP

```php
/*
 * @var string IP address
 */
$shieldon->banIP('33.125.12.87');

```

### setTraffic

```php
/*
 * @var integer Maximum amount of online vistors.
 * @var integer Period. (Unit: second)
 */
$shieldon->setTraffic(500, 300);
```

Not yet ready....
