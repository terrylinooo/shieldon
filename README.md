# Shieldon

![Selecton ](https://i.imgur.com/hOugMVT.png "Selecton ")

The project name is not to mean a Pokemon, Shieldon. It is "Shield" and "On". A PHP library that prevents anti-scraping, XSS filitering and traffic controll. As if a shield in front of attackers and protect your website.

## Get started

### Install

```
composer require terrylinooo/shieldon
```

### Example

Here is a simple example let you know how Shieldon works.

```
use Shieldon\Shieldon;

$shieldon = new Shieldon();

// Use Redis as the storage driver, but it is not required.
$shieldon->setDriver(new Shield\Driver\Redis([
    'ip' => 127.0.0.1,
    'port => 6319,
]));

// Ban IP: 33.125.12.87 immediately.
$shieldon->banIP('33.125.12.87');

// Remove XSS string from $_GET['keyword'];
$shielon->filtering('GET', 'keyword');

// Limit online traffic.
$shieldon->setTraffic(500, 300);

// Set a custom error page. It will display to the vistors who are blocked.
$shieldon->loadHtml($string);
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
