<?php

/*
  WineHQ
  by Jeremy Newman <jnewman@codeweavers.com>
*/  

/*
 * winehq misc utils
 */
 
// get a list of files in a directory
function get_files ($dir, $filter = null)
{
    // read dir
    $files = array();
    
    $d = opendir($dir);
    while($entry = readdir($d))
    {
    	if ($filter)
	    {
	        if(!eregi("(.+)\\.".$filter, $entry))
	            continue;
	    }
    	array_push($files, $entry);
    }
    closedir($d);
    
    //sort dir
    sort($files);
    
    return $files;
}

?>
