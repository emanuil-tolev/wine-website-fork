<?

/*
  Wine Development HQ - WWN Utils
*/  

$currentTag = "";
$issue = "";
$body = "";
$summary = array();
$person = array();
$map_array = array(
		   "p"		=> "p",
		   "br"		=> "br",
		   "a"		=> "a",
		   "b"		=> "b",
		   "i"		=> "i",
		   "u"		=> "u",
		   "ul"		=> "ul",
		   "ol"		=> "ol",
		   "li"		=> "li",
		   "table"	=> "table",
		   "tr"		=> "tr",
		   "td"		=> "td",
           "dl"     => "dl",
           "dt"     => "dt",
           "dd"     => "dd",
           "tt"     => "tt",
		   "code"	=> "code",
		   "blockquote"	=> "blockquote"	   
		  );

// read dir and get issues
function wwn_get_list ($path)
{
	$wwn = array();
	$dir = opendir($path);
	while($file = readdir($dir))
	{
		if ($file == "." or $file == ".." or $file == "CVS")
		  continue;
		$issue = ereg_replace("wn[0-9]+_([0-9]+)\.xml", "\\1", $file);
		if ($issue > $old)
		    $cur = $issue;
		$old = $issue;
		$wwn{$issue} = $file;
	}
	closedir($dir);
	return array($wwn, $cur);
}

// show back issues
function wwn_issues_list ($wwn, $cur, $limit = 0, $pos = 0)
{
	global $file_root, $config, $html;
	arsort($wwn);

	$perpage = 12;
	$total = count($wwn);
	$where = 0;

	$c = 0;
	$num = -1;
	
	if ($limit == 0)
	  $back = '<div align=center><table cellpadding=0 cellspacing=20 width="100%"><tr valign=top>'."\n";	
	
	while(list($issue,$file) = each($wwn))
	{	
		// inner counter
		$num++;

		if ($limit != 0 && $c == $limit)
		{
		  // break out if we hit our limit
		  break;
		}
		else if ($pos > $num)
		{
		  // do not show images less than current pos
		  continue;
		}

		// display summary
		$xml = wwn_xml_parse($config->wwn_xml_path."/".$file);
		$summary = wwn_format_summary($xml[1],$issue, $xml[2]);
		
		if ($limit == 0)
		  $back .= "\n\n<td>\n".$summary."\n</td>\n";
		else
		  $back .= "\n\n".$summary."\n";
		  
		// outer counter
		$c++;

		// end row at 3
		if (($limit == 0) && ($c % 3 == 0) && ($c != 1))
		{
			$back .= "</tr><tr valign=top>\n";
		}
		
		// end at max
		if ($c == $perpage)
		{
			$where = $num;
			break;
		}			
	}
	
	if ($limit == 0)
	  $back .= '</tr></table></div><br>'."\n";
	
	if ($limit != 0)
	{
		$back .= "<p><a href=\"".$PHP_SELF."?wwn=back"."\" class=small>More News...</a></p>";
	}
	else if ($total > $perpage)
	{
		$nextLink = "&nbsp;";
		$nextLink = "&nbsp;";

		// display prev link
		if ($pos >= $perpage)
		{
			$prev = $pos - $perpage;
			$prevLink = $html->ahref("&lt;&lt; Prev $perpage Issues",$PHP_SELF."?wwn=back;pos=".$prev,"class=menuItem");
		}
		else
		{
			$prevLink = "<font color=grey class=small>&lt;&lt; Prev $perpage Issues</font>";
		}

		// display next link
		if (($pos + $perpage) < $total)
		{
			$next = $where + 1;
			$nextLink = $html->ahref("Next $perpage Issues &gt;&gt;",$PHP_SELF."?wwn=back;pos=".$next,"class=menuItem");
		}
		else
		{
			$nextLink = "<font color=grey class=small>Next $perpage Issues &gt;&gt;</font>";
		}
		
	
		$back .= '<div align=center><table width="50%">'."\n";
		$back .= $html->frame_tr(
				    $html->frame_td("&nbsp; ".$prevLink,"align=left").
				    $html->frame_td($nextLink." &nbsp;","align=right"),
				    "color4"
				  );
		$back .= '</table></div>'."\n";
	}
		
	return $back;
}

// format the summary box
function wwn_format_summary ($array, $cur, $date = null)
{
	global $PHP_SELF;
    
	$summary = '<a href="'.$PHP_SELF."?wwn=".$cur.'"><b>Issue: '.$cur.'</b></a>'.
		       '<br><span class=small>'.$date.'</span><ul type="circle">'."\n";
	
    $c = 0;	    
	while(list($id,$sum) = each($array))
	{
		$summary .= '<li><a href="'.$PHP_SELF."?wwn=".$cur."#".$sum.'" class="small">'.$sum.'</a></li>'."\n";
		$c++;
	}
	$summary .= "</ul>";
	return $summary;
}

// display a single wwn issue
function wwn_view_issue($view)
{
    global $config, $html, $cur, $pos;

    // read dir and get issues
    list($wwn,$cur) = wwn_get_list($config->wwn_xml_path);

    // view back issues
    if ($view == 'back')
    {
        $html->template_title = 'WWN Back Issues';
        return $html->theme_box($config->theme, "box_title", 'WWN Back Issues', "100%", wwn_issues_list($wwn, $cur, 0, $pos), '10', 'white', 'topMenu');
    }
    
	// show selected issue, or current
	if ($view and $view != 'latest' and isset($wwn{$view}))
	  $cur = $view;
	
	// get issue
	list($wwn_html, $summary, $issue) = wwn_xml_parse($config->wwn_xml_path."/".$wwn{$cur});
	
    // title
    $html->template_title = 'WWN Issue #'.$cur;
    
	// get summary
	$summary_box = wwn_format_summary($summary, $cur);
	
    // title for page
    $title = 'WWN Issue '.$cur;
    
    // load issue into template
    $wwn_vars = array(
                      'issue'   => $cur,
                      'date'    => $issue,
                      'summary' => $summary_box,
                      'xml'     => $wwn{$cur},
                      'body'    => $wwn_html,
                     );
    $wwn_body = $html->template("base", "wwn", $wwn_vars);

    // load into page and return
    $text = $html->theme_box($config->theme, "box_title", $title, "100%", $wwn_body, '10', 'white', 'topMenu');

    return $text;
}

// read the xml file and parse it
function wwn_xml_parse ($file)
{
	global $issue;
	global $body;
	global $summary;

	$issue = "";
	$body = "";
	$summary = array();

	// initialize parser
	$xml_parser = xml_parser_create();

	// set callback functions
	xml_set_element_handler($xml_parser, "wwn_startElement", "wwn_endElement");
	xml_set_character_data_handler($xml_parser, "wwn_characterData");

	// open XML file
	if (!($fp = fopen($file, "r")))
	{
	    $body = "Cannot locate XML data file: $file";
	    return array($html, $summary);
	}

	// read and parse data
	while ($data = fread($fp, 16384))
	{
		// translate & entities
		$data = _translateLiteral2NumericEntities($data);
		$data = _translateWWN2valid($data);
		
	 	// error handler
		if (!xml_parse($xml_parser, $data, feof($fp)))
		{
			$xml_error = xml_error_string(xml_get_error_code($xml_parser));
			$xml_line = xml_get_current_line_number($xml_parser);
			$body = "<h1>XML error: $xml_error at line $xml_line</h1>";
			return array($body, $summary);
		}
	}

	// clean up
	xml_parser_free($xml_parser);

	return array($body, $summary, $issue);

}

// proccess elements in xml
function wwn_startElement ($parser, $name, $attrs)
{
	global $issue;
	global $html;
    global $body;
	global $currentTag;
	global $map_array;
	global $person;
	global $summary;

	// set current tag
	$currentTag = $name;
	
	// output opening HTML tags
	switch ($name)
	{
		case "ISSUE":
		  $issue = $attrs{'DATE'};
		  break;
		case "INTRO":
		  $body .= $html->frame_start("","","align=center", 0, "white");
		  $body .= "<tr class=frameBody><td colspan=3 bgcolor=\"#FFFFFF\">\n\n";
		  $body .= "<a name=\"Intro\"></a>\n";
        	  break;	
		case "SECTION":
		  array_push($summary, $attrs{'TITLE'});
		  $body .= $html->br();
		  $body .= $html->frame_start("","","align=center", 0, "white");
		  $body .= "<tr class=color0 bgcolor=\"#E0E0E0\">\n\n";
          $body .= "<td>".$attrs{'STARTDATE'}."</td>\n";
		  $body .= "<td align=center width=\"100%\"><b>".$attrs{'TITLE'}."</b></td>\n";
		  $body .= "<td align=right><a href=\"".$attrs{'ARCHIVE'}."\">Archive</a></td>\n";
		  $body .= "</tr>\n";
		  $body .= "<tr class=frameBody bgcolor=\"#FFFFFF\"><td colspan=3>\n\n";
		  $body .= "<a name=\"".$attrs{'TITLE'}."\"></a>\n";
        	  break;
		case "STATS":
		  $pctMTO = intval(($attrs{'MULTIPLES'} / $attrs{'CONTRIB'}) * 100);
		  $pctLWK = intval(($attrs{'LASTWEEK'} / $attrs{'CONTRIB'}) * 100);
		  $body .= $html->br();
		  $body .= $html->frame_start("","","align=center", 0, "white");
		  $body .= "<tr class=frameBody><td colspan=3 bgcolor=\"#FFFFFF\">\n\n";
		  $body .= "<p> This week, ".$attrs{'POSTS'}." posts consumed ".$attrs{'SIZE'}." K. ".
		          "There were ".$attrs{'CONTRIB'}." different contributors. ".
			  $attrs{'MULTIPLES'}." (".$pctMTO."%) posted more than once. ".
			  $attrs{'LASTWEEK'}." (".$pctLWK."%) posted last week too.</p>".
			  "<p>The top 5 posters of the week were:</p>";
		  break;
		case "PERSON":
		  $person{$attrs{'WHO'}} = array(
		  				 "POSTS" => $attrs{'POSTS'},
						 "SIZE" => $attrs{'SIZE'}
		  			        );
		  break;
		case "QUOTE":
		  $body .= "<blockquote><span class=\"wwnQuote\">";
		  break;  		  		  		  
		default:
		  if (isset($map_array[strtolower($name)]))
		  {
		    $attribs = "";
		    while(list($key,$value) = each($attrs))
		    {
		    	$attribs .= " $key=\"$value\"";
		    }
	  	    $body .= "<".$map_array[strtolower($name)].$attribs.">";		  
		  }
		  break;
	}
}

function wwn_endElement ($parser, $name)
{
	global $html;
    global $body;
	global $currentTag;
	global $map_array;
	global $person;
	
	// output closing HTML tags
	switch ($name)
	{
		case "INTRO":
		  $body .= "\n\n</td></tr>\n";
		  $body .= $html->frame_end("");
		  break;	
		case "SECTION":
		  $body .= "\n\n</td></tr>\n";
		  $body .= $html->frame_end("");
		  break;
		case "STATS":
		  $body .= "<ol>\n";
		  $c = 0;
          // FIXME :: NEED TO SORT THIS BY POSTS
		  while(list($id,$array) = each($person))
		  {
		  	if ($c == 5)
		  	  break;
			$body .= "<li>".$array{'POSTS'}." posts in ".
			         $array{'SIZE'}."K by ".$html->urlify($id)."</li>\n";
		  	$c++;
		  }
		  $body .= "</ol>\n";
		  $body .= "\n\n</tr></td>\n";
		  $body .= $html->frame_end("");
		  break;
		case "QUOTE":
		  $body .= "</span></blockquote>";
		default:
		  if (isset($map_array[strtolower($name)]))
	  	    $body .= "</".$map_array[strtolower($name)].">\n";			
		  break;
	}

	// clear current tag variable
	$currentTag = "";
}


// process data between tags
function wwn_characterData ($parser, $data)
{
	global $body;
	global $currentTag;

	// format the data
	switch ($currentTag)
	{
		case "TITLE":
		  break;
		case "AUTHOR":
		  break;		  
		default:
		  $body .= $data;
 		  break;	  
	}
}

// translate & entities to html numeric values
function _translateLiteral2NumericEntities ($xmlSource, $reverse = FALSE)
{
    static $literal2NumericEntity;    
    if (empty($literal2NumericEntity)) {
      $transTbl = get_html_translation_table(HTML_ENTITIES);
      foreach ($transTbl as $char => $entity) {
        if (strpos('&"<>', $char) !== FALSE) continue;
        $literal2NumericEntity[$entity] = '&#'.ord($char).';';
      }
    }
    if ($reverse) {
      return strtr($xmlSource, array_flip($literal2NumericEntity));
    } else {
      return strtr($xmlSource, $literal2NumericEntity);
    }
}

// fix other issues in wwn xml
function _translateWWN2valid ($xmlSource)
{
    $xmlSource = eregi_replace(" & ", " &#038; ", $xmlSource);
    $xmlSource = preg_replace("/=((\w|\.)+)&(\w|\.)+/", "\\1&#038;\\2=\\3", $xmlSource);
    $xmlSource = eregi_replace("<!-- .* -- />", "", $xmlSource);	
    return $xmlSource;
}

?>
