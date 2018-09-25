<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

define("ROOT_PATH", dirname(__DIR__));
define("DS", DIRECTORY_SEPARATOR);
define("APPS_PATH", ROOT_PATH . DS . "apps" . DS);
define("VENDOR_PATH", ROOT_PATH . DS ."vendor" . DS);

require_once APPS_PATH . DS . 'Core/Config.php';
require_once APPS_PATH . DS . 'Core/Loader.php';
require_once APPS_PATH . DS . 'Core/Application.php';

if(php_sapi_name() !== 'cli') {
    $app = new Kukulcan\Core\Application();
    
    $app->run();
}
