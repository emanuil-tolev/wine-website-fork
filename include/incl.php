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
require($file_root."/include/"."db.php");
require($file_root."/include/"."session.php");
require($file_root."/include/"."sidebar.php");
require($file_root."/include/"."winehq.php");
require($file_root."/include/"."wwn.php");
require($file_root."/include/"."utils.php");

// create html object
$html = new html($file_root);

// connect to store database
$db = new db($config->db_user, $config->db_pass, $config->db_host, $config->db_name);

// create winehq object (for misc var storage in session)
$winehq = new winehq();

// Init Session (stores crap into winehq session object)
$session = new session("winehq");
$session->register("winehq");

// load the theme from the session
if (isset($winehq->theme)) $config->theme = $winehq->theme;

// load the language from the session
if (isset($winehq->lang)) $config->lang = $winehq->lang;

// dump the session message buffer to user
$html->status_message = $session->dumpmsgbuffer();

// set the theme from get url
if (isset($theme) and in_array($theme, $config->themes))
{
    $config->theme = $theme;
    $winehq->theme = $theme;
}

// set the language from get url
if (isset($lang) and in_array($lang, $config->languages))
{
    $config->lang = $lang;
    $winehq->lang = $lang;
}

// load a banner ad
$html->banner_ad = banner_ad();

?>
