# PHP RESTful Client Library

## Synopsis
This is an easy to use client for [RESTful web services](https://en.wikipedia.org/wiki/Representational_state_transfer).

## Setup
### Installation with Composer.
Clone the repository.
```
$ git clone https://github.com/innesian/PhpRestClient.git
```
Install Composer in your project using cURL (command below) or [download the composer.phar directly](http://getcomposer.org/composer.phar).
```
$ curl -sS http://getcomposer.org/installer | php
```
Let Composer install the project dependencies:
```
$ php composer.phar install
```
Once installed, include the autoloader in your script.
```php
<?php
include_once 'vendor/autoload.php'; // Path to autoload.php file.
$rest = new \PhpRestClient\PhpRestClient('http://base.url/to/api/');
```
### (or) add PhpRestClient as a dependency to your REST project using Composer.
Create a *composer.json* file in your project and add `adam-innes/php-rest-client` as a required dependency.
```
{
    "require": {
        "adam-innes/php-rest-client": ">=1.0.1"
    }
}
```
