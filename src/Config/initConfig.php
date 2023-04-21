<?php
namespace App\Config;

use App\Routes\WebRoutes;

//site name
define('SITE_NAME', 'edifact-x12-ws');

//Error details
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('file_uploads', 1);
error_reporting(E_ALL);

//App Root
define('APP_ROOT', dirname(dirname(__FILE__)));
define('URL_ROOT', '/');
define('URL_SUBFOLDER', '/edifact-x12-ws');
session_start(); 

//Mysql Connection
define('DB_SERVER', '127.0.0.1:3306');
define('DB_USERNAME', 'edi_db_user');
define('DB_PASSWORD', 'test_pass12');
define('DB_NAME', 'edi_x12_db');
//include("dbConfig.php");


//Routes Invoke
$router = new Router();
$webRoutes = new WebRoutes();
$routes = $webRoutes->stablishRoutes();
$router($routes);
