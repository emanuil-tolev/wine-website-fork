<?php

/*
 * WineHQ.org
 * by Jeremy Newman <jnewman@codeweavers.com>
 */

/*
    Loads Google Analytics Statistics
*/

if (!empty($config->google_analytics))
{
    // display the google analytics code
    echo $html->template("local", "global/google_analytics", array('gcode' => $config->google_analytics));
}

// done
?>
