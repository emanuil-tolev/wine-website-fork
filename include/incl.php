<?php

/*
  WineHQ
  by Jeremy Newman <jnewman@codeweavers.com>

*/

/*
 * Main Include Library
 *
 */

// load config class
require($file_root."/include/"."config.php");

// create config object
$config = new config();

// load global libs
require($file_root."/include/"."html.php");
require($file_root."/include/"."sidebar.php");
require($file_root."/include/"."wwn.php");
require($file_root."/include/"."utils.php");

// create html object
$html = new html($file_root);

// load the theme from the session
if (isset($_COOKIE['winehq']) and in_array($_COOKIE['winehq'], $config->themes))
{
    $config->theme = $_COOKIE['winehq'];
}

// set the theme from get url
if (isset($_GET['theme']) and in_array($_GET['theme'], $config->themes))
{
    setcookie("winehq", $_GET['theme'], time()+((3600*12)*365), "/");
    $config->theme = $_GET['theme'];
}

// load a banner ad
$html->banner_ad = banner_ad();

?>
