# laravel-qunjielong

A Qunjielong Laravel Library Developed By Guolei

# Installation

```shell
composer require guolei19850528/laravel-qunjielong
```
# Example
```php
use Guolei19850528\Laravel\Qunjielong\Qunjielong;

$qunjielong = new Qunjielong('your secret');
$qunjielong->tokenWithCache()->getGhomeInfo();
```
