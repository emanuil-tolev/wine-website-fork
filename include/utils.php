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

// get front page news listing
function get_news ()
{
    global $config, $html;
      
    // get list of news items
    $n = array();
    $news = get_files($config->news_xml_path, "xml");
    $news = array_reverse ($news);
    
    // loop and display news
    $c = 0;
    foreach ($news as $key => $item)
    {
        // counter
        $c++;
        
        // get data from XML file
        $vars = array();
        list($vars['date'], $vars['title'], $vars['body']) = get_xml_tags($config->news_xml_path.'/'.$item, array('date', 'title', 'body'));

        // add to news body
        $news_body .= $html->template('base', 'news_row', $vars);
        
        // only show 5 records
        if ($c == 4 && !$_GET['shownews'])
        {
            $news_body .= $html->p($html->ahref('More News', '?shownews=archive'));
            break;
        }
    } // end of news loop
    
    // return the finished body
    return $news_body;
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

?>
