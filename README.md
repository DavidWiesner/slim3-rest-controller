# Simple REST Controller for [SLIM 3](http://www.slimframework.com/)
[![Travis branch](https://img.shields.io/travis/DavidWiesner/slim3-rest-controller/master.svg?style=flat-square)](https://travis-ci.org/DavidWiesner/oauth2-server-pdo) [![Codecov](https://img.shields.io/codecov/c/github/DavidWiesner/oauth2-server-pdo.svg?style=flat-square)](https://codecov.io/github/DavidWiesner/slim3-rest-controller?branch=master) [![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

## Dependencies
 * [dboho/simple-database-api](https://github.com/DavidWiesner/simple-database-api)
 * [slim/slim >= 3.0](http://www.slimframework.com/)
 * PHP >= 5.5

## Usage


```php
// dependencies container
$container = $app->getContainer();

$container[TableController::class] = function ($c) {
    $pdo = new PDO('sqlite:database.db');
    $dataAccess = new DataAccess($pdo);
    return new TableController($dataAccess);
};

// routes for tables books, videos and images
$app->group('/api/{table:books|videos|images}', function () {

    // get all entries in books or a subset selected with query-parameters
    $this->get('', TableController::class . ':getAll');
    
    // get one entry
    $this->get('/{id:[0-9]+}', TableController::class . ':get');
    
    // add one entry
    $this->post('', TableController::class . ':add');
    
    // update one entry
    $this->put('/{id:[0-9]+}', TableController::class . ':update');
    
    // update all entries or a subset selected with query-parameters
    $this->put('', TableController::class . ':update');
    
    // delete a specific entry
    $this->delete('/{id:[0-9]+}', TableController::class . ':delete');
    
    // delete all entries or a subset selected with query-parameters
    $this->delete('', TableController::class . ':delete');
});
```
## Installation

The recommended installation method is via [Composer](https://getcomposer.org/).

In your project root just run:

```bash
$ composer require dboho/slim3-rest-controller
```
