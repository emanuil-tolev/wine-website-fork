<?

/*
  Wine Development HQ - WWN Utils
*/  

class wwn 
{
    var $currentTag;
    var $issue;
    var $who;
    var $author;
    var $email;
    var $body;
    var $summary;
    var $person;
    var $map_array;
    
    // init wwn object
    function wwn ()
    {
        $this->summary = array();
        $this->person = array();
        $this->map_array = array(
                   "p"	    => "p",
                   "br"	    => "br",
                   "a"	    => "a",
                   "b"	    => "b",
                   "i"	    => "i",
                   "u"	    => "u",
                   "ul"	    => "ul",
                   "ol"	    => "ol",
                   "li"	    => "li",
                   "table"  => "table",
                   "tr"	    => "tr",
                   "td"	    => "td",
                   "th"     => "th",
                   "dl"     => "dl",
                   "dt"     => "dt",
                   "dd"     => "dd",
                   "tt"     => "tt",
                   "code"   => "code",
                   "blockquote"	=> "blockquote"	   
                  );
    }
    
    // read dir and get issues
    function get_list ($path)
    {
        $wwn = array();
        $dir = opendir($path);
        while($file = readdir($dir))
        {
            if ($file == "." or $file == ".." or $file == "CVS" or $file == "interviews")
              continue;
            $issue = ereg_replace("wn[0-9]+_([0-9]+)\.xml", "\\1", $file);
            if ($issue > $old)
                $cur = $issue;
            $old = $issue;
            $wwn[$issue] = $file;
        }
        closedir($dir);
        return array($wwn, $cur);
    }

    // read dir and get interviews
    function get_interviews ($path)
    {
        $wwn = array();
        $dir = opendir($path);
        while($file = readdir($dir))
        {
            if ($file == "." or $file == ".." or $file == "CVS")
              continue;
            $issue = ereg_replace("interview_([0-9]+)\.xml", "\\1", $file);
            if ($issue > $old)
                $cur = $issue;
            $old = $issue;
            $wwn[$issue] = $file;
        }
        closedir($dir);
        return array($wwn, $cur);
    }

    // show back issues
    function issues_list ($issues, $cur, $limit = 0, $pos = 0)
    {
        global $file_root, $config, $html;
        arsort($issues);
    
        $perpage = 12;
        $total = count($issues);
        $where = 0;
    
        $c = 0;
        $num = -1;
        
        if ($limit == 0)
          $back = '<div align=center><table cellpadding=0 cellspacing=20 width="100%"><tr valign=top>'."\n";	
        
        while(list($issue,$file) = each($issues))
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
            $this->wwn_xml_parse($config->wwn_xml_path."/".$file);
            $summary_box = $this->format_summary($issue, $this->issue);
            
            if ($limit == 0)
              $back .= "\n\n<td>\n".$summary_box."\n</td>\n";
            else
              $back .= "\n\n".$summary_box."\n";
              
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
            $back .= "<p><a href=\"".$PHP_SELF."?issue=back"."\" class=small>More Issues...</a></p>";
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
                $nextLink = $html->ahref("Next $perpage Issues &gt;&gt;",$PHP_SELF."?issue=back;pos=".$next,"class=menuItem");
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
    function format_summary ($cur, $date = null)
    {
        global $PHP_SELF;
        
        $summary_box = '<a href="'.$PHP_SELF."?issue=".$cur.'"><b>Issue: '.$cur.'</b></a>'.
                       '<br><span class=small>'.$date.'</span><ul type="circle">'."\n";
        
        $c = 0;
        while(list($id,$sum) = each($this->summary))
        {
            $summary_box .= '<li><a href="'.$PHP_SELF."?issue=".$cur."#".$sum.'" class="small">'.$sum.'</a></li>'."\n";
            $c++;
        }
        $summary_box .= "</ul>";
        return $summary_box;
    }
    
    // display a single wwn issue
    function view_issue ($view = "latest")
    {
        global $config, $html, $cur, $pos;
    
        // read dir and get issues
        list($wwn, $cur) = $this->get_list($config->wwn_xml_path);
    
        // view back issues
        if ($view == 'back')
        {
            $html->template_title = 'WWN Back Issues';
            return $html->theme_box($config->theme, "box_title", 'WWN Back Issues', "100%", $this->issues_list($wwn, $cur, 0, $pos), '10', 'white', 'topMenu');
        }
        
        // show selected issue, or current
        if ($view and $view != 'latest' and $wwn[$view])
          $cur = $view;

        // get issue
        $this->wwn_xml_parse($config->wwn_xml_path."/".$wwn[$cur]);
                
        // get summary
        $summary_box = $this->format_summary($cur);
        
        // title for page
        $html->template_title = 'WWN Issue #'.$cur;
        
        // load issue into template
        $wwn_vars = array(
                          'issue'   => $cur,
                          'date'    => $this->issue,
                          'summary' => $summary_box,
                          'xml'     => $wwn{$cur},
                          'author'  => $this->author,
                          'body'    => $this->body,
                         );
        $wwn_body = $html->template("base", "wwn", $wwn_vars);
    
        // load into page and return
        $text = $html->theme_box($config->theme, "box_title", $html->template_title, "100%", $wwn_body, '10', 'white', 'topMenu');
    
        return $text;
    }
    
    // display a single wwn issue
    function view_interview($interview)
    {
        global $config, $html;
    
        // read dir and get issues
        list($wwn, $cur) = $this->get_interviews($config->wwn_xml_path."/interviews/");

        // no interview found, show a 404
        if (!$interview or !$wwn[$interview])
            return $html->theme_box($config->theme, "box_title", "404 Not Found", "100%", $html->template("base", "404"), '10', 'white', 'topMenu');
        
        // get issue
        $this->wwn_xml_parse($config->wwn_xml_path."/interviews/".$wwn[$interview]);
        
        // title for page
        $html->template_title = $this->who.' Interview';
        
        // load issue into template
        $wwn_vars = array(
                          'who'    => $this->who,
                          'date'   => $this->issue,
                          'author' => $html->ahref($this->author, 'mailto:'.$this->email),
                          'body'   => $this->body,
                         );
        $wwn_body = $html->template("base", "wwn_interview", $wwn_vars);
    
        // load into page and return
        $text = $html->theme_box($config->theme, "box_title", $html->template_title, "100%", $wwn_body, '10', 'white', 'topMenu');
    
        return $text;
    }
    
    // read the xml file and parse it
    function wwn_xml_parse ($file)
    {    
        $this->issue = "";
        $this->body = "";
        $this->who = "";
        $this->summary = array();
    
        // initialize parser
        $xml_parser = xml_parser_create();
    
        // set callback functions
        xml_set_object($xml_parser, &$this);
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler($xml_parser, "characterData");
    
        // open XML file
        if (!($fp = fopen($file, "r")))
        {
            $this->body = "Cannot locate XML data file: $file";
            return;
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
                $this->body = "<h1>XML error: $xml_error at line $xml_line</h1>";
                return;
            }
        }
    
        // clean up
        xml_parser_free($xml_parser);
    }
    
    // process elements in xml
    function startElement ($parser, $name, $attrs)
    {
        global $html;
    
        // set current tag
        $this->currentTag = $name;
        
        // output opening HTML tags
        switch ($name)
        {
            case "ISSUE":
              $this->issue = $attrs{'DATE'};
              $this->who = $attrs{'WHO'};
              break;
            case "AUTHOR":
              $this->email = $attrs{'EMAIL'};
              break;
            case "INTRO":
              $this->body .= "<a name=\"Intro\"></a>\n";
              break;	
            case "SECTION":
              array_push($this->summary, $attrs{'TITLE'});
              $this->body .= $html->br();
              $this->body .= $html->frame_start("","100%","align=center", 0, "white");
              $this->body .= "<tr class=color0 bgcolor=\"#E0E0E0\">\n\n";
              $this->body .= "<td>".$attrs{'STARTDATE'}."</td>\n";
              $this->body .= "<td align=center width=\"100%\"><b>".$attrs{'TITLE'}."</b></td>\n";
              $this->body .= "<td align=right><a href=\"".$attrs{'ARCHIVE'}."\">Archive</a></td>\n";
              $this->body .= "</tr>\n";
              $this->body .= "<tr class=frameBody bgcolor=\"#FFFFFF\"><td colspan=3>\n\n";
              $this->body .= "<a name=\"".$attrs{'TITLE'}."\"></a>\n";
              break;
            case "STATS":
              $pctMTO = intval(($attrs{'MULTIPLES'} / $attrs{'CONTRIB'}) * 100);
              $pctLWK = intval(($attrs{'LASTWEEK'} / $attrs{'CONTRIB'}) * 100);
              $this->body .= $html->br();
              $this->body .= $html->frame_start("","","align=center", 0, "white");
              $this->body .= "<tr class=frameBody><td colspan=3 bgcolor=\"#FFFFFF\">\n\n";
              $this->body .= "<p> This week, ".$attrs{'POSTS'}." posts consumed ".$attrs{'SIZE'}." K. ".
                      "There were ".$attrs{'CONTRIB'}." different contributors. ".
                  $attrs{'MULTIPLES'}." (".$pctMTO."%) posted more than once. ".
                  $attrs{'LASTWEEK'}." (".$pctLWK."%) posted last week too.</p>".
                  "<p>The top 5 posters of the week were:</p>";
              break;
            case "PERSON":
              array_push(
                         $this->person,
                         array(
                               "WHO"   => $attrs{'WHO'},
                               "POSTS" => $attrs{'POSTS'},
                               "SIZE"  => $attrs{'SIZE'}
                              )
                        );
              break;
            case "QUOTE":
              $this->body .= "<blockquote><span class=\"wwnQuote\">";
              break;
            case "INTERVIEW":
              break;
            case "QUESTION":
              $this->body .= "<p><b>";
              break;
            case "ANSWER":
              $this->body .= "<blockquote><span class=\"wwnQuote\">";
              break;
            default:
              if (isset($this->map_array[strtolower($name)]))
              {
                $attribs = "";
                while(list($key,$value) = each($attrs))
                {
                    $attribs .= " $key=\"$value\"";
                }
                $this->body .= "<".$this->map_array[strtolower($name)].$attribs.">";		  
              }
              break;
        }
    }
    
    function endElement ($parser, $name)
    {
        global $html;
        
        // output closing HTML tags
        switch ($name)
        {
            case "INTRO":
              break;	
            case "SECTION":
              $this->body .= "\n\n</td></tr>\n";
              $this->body .= $html->frame_end("");
              break;
            case "STATS":
              $this->body .= "<ol>\n";
              $c = 0;
              array_qsort($this->person, "POSTS", SORT_DESC);
              foreach ($this->person as $array)
              {
                if ($c == 5)
                  break;
                $this->body .= "<li>".$array['POSTS']." posts in ".
                               $array['SIZE']."K by ".
                               $html->urlify($array['WHO'])."</li>\n";
                $c++;
              }
              $this->body .= "</ol>\n";
              $this->body .= "\n\n</tr></td>\n";
              $this->body .= $html->frame_end("");
              break;
            case "QUOTE":
              $this->body .= "</span></blockquote>";
              break;
            case "INTERVIEW":
              
              break;
            case "QUESTION":
              $this->body .= "</b></p>";
              break;
            case "ANSWER":
              $this->body .= "</span></blockquote>";
              break;
            default:
              if (isset($this->map_array[strtolower($name)]))
                $this->body .= "</".$this->map_array[strtolower($name)].">\n";			
              break;
        }
    
        // clear current tag variable
        $this->currentTag = "";
    }
    
    // process data between tags
    function characterData ($parser, $data)
    {    
        // format the data
        switch ($this->currentTag)
        {
            case "TITLE":
              break;
            case "AUTHOR":
              $this->author = $data;
              break;		  
            default:
              $this->body .= $data;
              break;	  
        }
    }

}
// end wwn class

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
