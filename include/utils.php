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
    sort($ads);
    
	// randomly select a banner and display it
	$img = $ads[(rand(1,count($ads))-1)];
	list($url, $alt) = get_xml_tags($bannerads_path.$img.'.xml', array('url', 'alt'));

	// da banner
	$banner = $html->ahref($html->img('bannerads/'.$img.".gif", "", $alt), $url);	

	return $banner;
}

// open file and display contents of selected tag (very simple)
function get_xml_tags ($file, $tags = null)
{
    if (is_array($tags) and file_exists($file))
    {
        $content = array();
        $fp = @fopen($file, "r");
	    $data = fread($fp, filesize($file));
	    @fclose($fp);
        foreach ($tags as $tag)
        {
            if (eregi("<" . $tag . ">(.*)</" . $tag . ">", $data, $out))
            {
                array_push($content, $out[1]);
            }
        }
        return $content;
    }
    else
    {
        return null;
    }
}

// sort a multi-dimentional array by one of the keys
function array_qsort (&$array, $column=0, $order=SORT_ASC, $first=0, $last= -2)
{
    // $array  - the array to be sorted
    // $column - index (column) on which to sort
    //          can be a string if using an associative array
    // $order  - SORT_ASC (default) for ascending or SORT_DESC for descending
    // $first  - start index (row) for partial array sort
    // $last   - stop index (row) for partial array sort
    
    if($last == -2) $last = count($array) - 1;
    if($last > $first)
    {
        $alpha = $first;
        $omega = $last;
        $guess = $array[$alpha][$column];
        while ($omega >= $alpha) {
            if($order == SORT_ASC)
            {
                while($array[$alpha][$column] < $guess) $alpha++;
                while($array[$omega][$column] > $guess) $omega--;
            }
            else
            {
                while($array[$alpha][$column] > $guess) $alpha++;
                while($array[$omega][$column] < $guess) $omega--;
            }
            if($alpha > $omega) break;
            $temporary = $array[$alpha];
            $array[$alpha++] = $array[$omega];
            $array[$omega--] = $temporary;
        }
        array_qsort($array, $column, $order, $first, $omega);
        array_qsort($array, $column, $order, $alpha, $last);
    }
}

?>
