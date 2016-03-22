<?php
/**
 * Created by IntelliJ IDEA.
 * User: david
 * Date: 07.03.16
 * Time: 10:26
 */
namespace DBohoTest\Slim\Controller;
define('SLIM_MODE', 'test');

use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Http\Uri;

error_reporting(E_ALL);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);
// To help the built-in PHP dev server, check if the request was actually for
// something which should probably be served as a static file
if (PHP_SAPI === 'cli-server' && $_SERVER['SCRIPT_FILENAME'] !== __FILE__) {
    return false;
}

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
