<?php

/*
  WineHQ
  by Jeremy Newman <jnewman@codeweavers.com>

*/

/*
 * Main Include Library
 */

// Require PHP version 5.3 or higher
if (version_compare(phpversion(), '5.3.0') < 0)
{
    trigger_error("PHP 5.3 or Higher Required!", E_USER_ERROR);
}

// load global functions lib
require_once("{$file_root}/include/utils.php");

// Set Up the Class AutoLoader
spl_autoload_register("check_and_require");

// create config object
$config = new config("{$file_root}/include/winehq.conf", "{$file_root}/include/globals.conf");

// load data lib
$data = new data();

// create html object
$html = new html($file_root);

// setup html error handler when not at CLI
if (!defined('STDIN'))
{
    set_error_handler(array(&$html, 'error_handler'));
    register_shutdown_function(array(&$html, 'error_shutdown'));
}

?>
