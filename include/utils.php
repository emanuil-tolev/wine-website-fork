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

// load and display banner ads
function banner_ad ()
{
	global $file_root, $html;
    $bannerads_path = $file_root.'/images/bannerads/';

	// read dir and get list of banners
	$ads = array();
	$d = opendir($bannerads_path);
	while($entry = readdir($d))
	{
		if(!ereg("(.+)\\.gif$", $entry, $arr))
			continue;
		array_push($ads, $arr[1]);
	}
	closedir($d);
	
	// randomly select a banner and display it
	$img = $ads[(rand(1,count($ads))-1)];
	$url = get_xml_tag($bannerads_path.$img.'.xml','url');
	$alt = get_xml_tag($bannerads_path.$img.'.xml','alt');

	// da banner
	$banner = $html->ahref($html->img('bannerads/'.$img.".gif", "", $alt), $url);	

	return $banner;
}

// open file and display contents of selected tag (very simple)
function get_xml_tag ($file, $mode = null)
{
    if ($mode and file_exists($file))
    {
        $fp = @fopen($file, "r");
	    $data = fread($fp, filesize($file));
	    @fclose($fp);
	    if (eregi("<" . $mode . ">(.*)</" . $mode . ">", $data, $out))
	    {
	        return $out[1];
 	    }
    }
    else
    {
        return null;
    }
}

?>
