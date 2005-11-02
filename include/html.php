<?php

/*
  WineHQ
  by Jeremy Newman <jnewman@codeweavers.com>
*/  

/*
    HTML class
*/
class html 
{
    var $_indent_level;
    var $_trcolor;
    var $_file_root;
    var $page;
    var $template_title;
    var $in404;

    // HTML init object
    function html ($root)
    {
        // set the file root
        $this->_file_root = $root;
        // start body object
        $this->page = "";
    }

    // FIND ROOT (based on path info, get the web root)
    function find_root ()
    {
        $d = $_SERVER['PATH_INFO'];
        if ($d)
        {
            $dirs = split('/',$d);
            for ($c=0;$c<(count($dirs)-1);$c++)
            {
                if ($n)
                    $n .= '/';
                $n .= '..';
            }
            return $n;
        }
        else
        {
            return $this->_file_root;
        }
    }

    // SHOWPAGE (display the current page)
    function showpage ($theme, $title)
    {
        global $config, $page, $view;
        if (!$this->_error_mode)
        {
            // only show nav bar in non-error modes
            $sidebar = new sidebar($config->theme, $config->nav, $this->find_root());
            $menu = $sidebar->menu();
        }
        $body = $this->status_message;
        $body .= $this->page;
        if ($config->web_debug and $this->errorlog)
        {
            $body .= $this->br().$this->frame_start("Debug Log", "95%");
            $body .= $this->frame_tr($this->frame_td($this->span($this->errorlog,"class=tiny"),"class=ltgrey"));
            $body .= $this->frame_end("");
        }
        $search = $this->template("base", "search");
        // display the html
        if ($this->in404)
        {
            // 404 not found
            header("HTTP/1.1 404 Not Found");
        }
        else
        {
            // normal HTTP Headers
            $this->http_header();
        }
        echo 
        $this->template(
                        $theme,
                        "content",
                        array(
                              'page_title'    => $title,
                              'page_body'     => $body,
                              'page_sidebar'  => $menu,
                              'page_search'   => $search,
                              'banner_ad'     => $this->banner_ad
                             )
                );
    }

    function error_page ($message = null)
    {
        global $config;
        // set error mode
        $this->_error_mode = 1;
        // clear the current page in progress
        $this->page = "";
        // create error page
        $this->page .= $this->br(2);
        $this->page .= $this->frame_start("", "50%");
        $this->page .= $this->frame_tr(
                                       $this->frame_td(
                                                       $this->p("<big><b><font color=red>Error</font></b></big>").
                                                       $this->line().$this->p($message).$this->line().        
                                                       $this->p("Please contact the ".
                                                       $this->ahref("webmaster","mailto:".$config->site_admin).
                                                       " about this error.")                                       
                                                      ),
                                       "white",
                                       "align=center"
                                      ); 
        $this->page .= $this->frame_end("");
        // show page
        $this->showpage($config->theme, $config->title." - Internal Error");
        exit();
    }

    // DO INDENT (internal indent)
    function do_indent ($str, $v = 0)
    {
	    if($v < 0)
		    $this->_indent_level += $v;

	    if($this->_indent_level > 0)
	        $str =  str_repeat("  ", $this->_indent_level) . $str;

	    if($v > 0)
		    $this->_indent_level += $v;

	    return $str . "\n";
    }

    // HTML BR
    function br ($count = 1)
    {
	    return $this->do_indent(str_repeat("<br />", $count));
    }
    
    // HTML IMG Tag
    function img ($src, $align = "", $alt = "-", $width = null, $height = null)
    {
     	if ($align) $doAlign = ' align="'.$align.'"';
        if ($alt) $doAlt = ' alt="'.$alt.'"';
        if ($src and file_exists($this->_file_root."/images/".$src))
        {
            // load image from images dir
            $size = getimagesize($this->_file_root."/images/".$src);
            return '<img src="'.$this->find_root()."/images/".$src.'" border="0" '."{$size[3]}".$doAlign.$doAlt.' />';
        }
        else if ($src)
        {
            // load other image
            if ($width) $width = ' width="'.$width.'"';
            if ($height) $height = ' height="'.$height.'"';
            return '<img src="'.$src.'" border="0"'.$width.$height.$doAlign.$doAlt.' />';
        }
    }
    
    // HTML A HREF
    function ahref ($label, $url = "", $extra = "")
    {
	    $label = stripslashes($label);
	    if (!$label and $url)
	    {	
		    if (ereg("@",$url))
		    {
			    return $this->do_indent(" <a href=\"mailto:$url\" $extra>$url</a> ");
		    }
		    else
		    {
			    return $this->do_indent(" <a href=\"$url\" $extra>$url</a> ");
		    }
	    }
	    else if (!$label)
	    {
		    return $this->do_indent(" &nbsp; ");
	    }
	    else
	    {
		    return $this->do_indent(" <a href=\"$url\" $extra>$label</a> ");
	    }
    }

    // HTML B (bold)
    function b ($str)
    {
	    return $this->do_indent("<b>$str</b>");
    }

    // HTML SMALL (small text)
    function small ($str)
    {
	    return $this->do_indent("<small>$str</small>");
    }

    // HTML P
    function p ($str = "&nbsp;", $extra = null)
    {
	    return $this->do_indent("<p $extra>$str</p>");
    }

    // HTML DIV
    function div ($str = "&nbsp;", $extra = null)
    {
	    return $this->do_indent("<div $extra>$str</div>");
    }
    
    // HTML SPAN
    function span ($str = "&nbsp;", $extra = null)
    {
	    return $this->do_indent("<span $extra>$str</span>");
    }
    
    // HTML PRE
    function pre ($str = "&nbsp;", $extra = null)
    {
	    return "<pre $extra>$str</pre>";
    }

    // LINE
    function line ($width = "100%", $height = 1, $color = "grey")
    {
        return '<img src="'.$this->_file_root.'/images/'.$color.'_pixel.gif" height="'.$height.'" width="'.$width.'" alt="-">';
    }

    // TABLE
    function frame_table ($body = "", $width = "", $innerPad = 5, $extra = "")
    {
        if ($width) { $width = 'width="'.$width.'"'; }
        $str .= '<div align=center><table '.$width.' border="0" cellpadding="'.$pad.'" cellspacing="0" '.$extra.'>'."\n";
        $str .= $body;
        $str .= '</table></div>'."\n";
        return $str;
    }

    // COOL FRAME START
    function frame_start ($title = "", $width = "", $extra = "", $innerPad = 5, $class = "topMenu")
    {
        if ($width) { $width = 'width="'.$width.'"'; }
        $str = '<div align=center>'."\n";
        $str .= '<table '.$width.' border=0 cellpadding=0 cellspacing=0>'."\n";
        if($title)
        {
    	    $str .= '<tr><td class="'.$class.'">'."\n";
            $str .= '    <table width="100%" border=0 cellpadding=0 cellspacing=0><tr>'."\n";
    	    $str .= '        <td width="100%" align=center><span class=menuTitle> '.$title.' </span></td>'."\n";
            $str .= '    </tr></table>'."\n";
    	    $str .= '</td></tr>'."\n";
        }
        $str .= '<tr><td class="'.$class.'">'."\n";	
        $str .= '<table width="100%" border=0 cellpadding='.$innerPad.' cellspacing=1 '.$extra.'>'."\n";
        return $str;
    }

    // COOL FRAME END
    function frame_end ($text = "")
    {
        $str .= '</table></td></tr>'."\n";
        if ($text)
        {
    	    $str .= '<tr><td class=topMenu>'."\n";
            $str .= '    <table width="100%" border=0 cellpadding=0 cellspacing=0><tr>'."\n";
    	    $str .= '        <td width="100%" align=center><span class=menuTitle> '.$text.' </span></td>'."\n";
            $str .= '    </tr></table>'."\n";
            $str .= '</td></tr>'."\n";
        }
        $str .= "</table></div><br>\n";
        $this->_trcolor = 0;
        return $str;
    }

    // fill a theme based box with content
    function theme_box ($theme, $template, $title = "", $width = "100%", $body = "", $pad = "10", $fill = "white", $border = "topMenu")
    {
        $vars = array(
                      'width' => $width,
                      'title' => $title,
                      'body' => $body,
                      'pad' => $pad,
                      'fill' => $fill,
                      'border' => $border
                     );
        return $this->template($theme, $template, $vars);             
    }

    // COOL FRAME ROW START
    function frame_row_start ($color = "white", $extra = null)
    {
        $str = '<tr class="'.$color.'" '.$extra.'><td>'."\n";
        return $str;
    }

    // COOL FRAME ROW END
    function frame_row_end ()
    {
        return '</td></tr>'."\n";
    }

    // COOL FRAME TR
    function frame_tr ($td = "<td>&nbsp;</td>", $color = "", $extra = null)
    {
        if ($color) $color = 'class="'.$color.'"';
        $str = '<tr valign="top"'.$color.' '.$extra.'>'."\n".$td.'</tr>'."\n";
        return $str;
    }

    // COOL FRAME TD
    function frame_td ($txt = "&nbsp;", $extra = null)
    {
        $str = '<td '.$extra.'>'.$txt.'</td>'."\n";
        return $str;
    }

    // COOL FRAME ROW FOR FORM USAGE
    function frame_row_form ($name = "&nbsp;", $field = "&nbsp;")
    {
        $str  = '    <tr valign=top><td class="ltgrey" width="200"><b class="small">'.$name.'</b></td>'."\n";
        $str .= '    <td class="white" width="100%">'.$field.'</td></tr>'."\n";
        return $str;
    }

    // COOL FRAME ROW TITLE FOR FORMS
    function frame_row_form_title ($name = "&nbsp;", $extra = null)
    {
        $str  = '    <tr valign=top><td class="white" colspan=2 '.$extra.'>'.$name.'</td></tr>'."\n";
        return $str;
    }

    // FORM START
    function form_start ($script, $name, $method = "get")
    {
	    $str = '<form name="'.$name.'" action="'.$script.'" method="'.$method.'">'."\n";
	    return $str;
    }

    // FORM END
    function form_end ()
    {
	    return '</form>'."\n";
    }

    // FORM INPUT TEXT
    function form_input_text ($name, $size = 20, $value = "", $max = 0)
    {
        if ($max)
            $maxlengh = ' maxlength="'.$max.'"';
	    $str = '<input type=text name="'.$name.'" size="'.$size.'" value="'.$value.'"'.$maxlengh.'>'."\n";
	    return $str;
    }

    // FORM INPUT PASSWORD
    function form_input_password ($name, $size = 20, $value = "")
    {
	    $str = '<input type=password name="'.$name.'" size="'.$size.'" value="'.$value.'">'."\n";
	    return $str;
    }

    // FORM INPUT HIDDEN FIELD
    function form_input_hidden ($name, $value = "")
    {
	    $str = '<input type=hidden name="'.$name.'" value="'.$value.'">'."\n";
	    return $str;
    }

    // FORM INPUT TEXT AREA
    function form_input_textarea ($name, $cols = 20, $rows = 5, $value = "")
    {
	    $str = '<textarea name="'.$name.'" cols="'.$cols.'" rows="'.$rows.'" wrap="soft">'.$value.'</textarea>'."\n";
	    return $str;
    }

    // FORM INPUT SELECT (drop down)
    function form_input_select ($name, $options, $selected = "", $size = 1, $multi = null)
    {
        if ($multi)
            $multi = " multiple";
	    $str = '<select name="'.$name.'" size="'.$size.'"'.$multi.'>'."\n";
	    while (list($key, $val) = each($options))
	    {	
		    if ($key == $selected)
		    {
			    $str .= '<option value="'.$key.'" selected>'.$val.'</option>'."\n";
		    }
            else
            {
			    $str .= '<option value="'.$key.'">'.$val.'</option>'."\n";		
		    }
	    }
	    $str .= '</select>'."\n";
	    return $str;
    }

    // FORM INPUT TIMESTAMP (make an input field for timestamp)
    function form_input_timestamp ($fn, $timestamp)
    {
        $months = array(1 => 'January', 'February', 'March', 'April', 'May',
                      'June', 'July', 'August', 'September', 'October',
                      'November', 'December');          
        // month
        $ascvar = $fn . "[1]";
        $ret.="  <select name=\"$ascvar\">\n";
        $cur = 1;
        while ($cur <= 12)
        {
            if (date("n",$timestamp) == $cur) { $selected = " SELECTED"; }
            $ret.="    <option value=$cur$selected>$months[$cur]</option>\n";
            $cur++;
            $selected = "";
        }
        $ret.="  </select>\n";
        
        // day
        $ascvar = $fn . "[2]";
        $ret.="  <select name=\"$ascvar\">\n";
        $cur = 1;
        while ($cur <= 31)
        {
            if (date("j",$timestamp) == $cur) { $selected = " SELECTED"; }
            $ret.="    <option value=$cur$selected>$cur</option>\n";
            $cur++;
            $selected = "";
        }
        $ret.="  </select>\n";
        
        // year
        $ascvar = $fn . "[0]";
        $ret.="  <select name=\"$ascvar\">\n";
        $cur = "1969";
        while ($cur <= (date("Y")+5))
        {
            if (date("Y",$timestamp) == $cur) { $selected = " SELECTED"; }
            $ret.="    <option value=$cur$selected>$cur</option>\n";
            $cur++;
            $selected = "";
        }
        $ret.="  </select>";
        
        // hour
        $ascvar = $fn . "[3]";
        $ret.="  <select name=\"$ascvar\">\n";
        $cur = "1";
        while ($cur <= "24")
        {
            if (date("G",$timestamp) == $cur) { $selected = " SELECTED"; }
            $ret.="    <option value=$cur$selected>$cur</option>\n";
            $cur++;
            $selected = "";
        }
        $ret.="  </select>";
        
        // min
        $ascvar = $fn . "[4]";
        $ret.=":<select name=\"$ascvar\">\n";
        $cur = "0";
        while ($cur <= "59")
        {
            if ($cur < "10") { $cur = "0".$cur; }
            if (date("i",$timestamp) == $cur) { $selected = " SELECTED"; }
            $ret.="    <option value=$cur$selected>$cur</option>\n";
            $cur++;
            $selected = "";
        }
        $ret.="  </select>";
        
        return $ret;
    }
 
    // PROC INPUT TIMESTAMP (process timestamp field)
    function proc_input_timestamp ($fn)
    {
        $lfn = $GLOBALS["$fn"];
        $strdate = $lfn[0]."-".$lfn[1]."-".$lfn[2]." ".$lfn[3].":".$lfn[4];
        if ($strdate != "--")
        {
            $timestamp = strtotime($strdate);
            return $timestamp;
        }
        else
        {
            return -1;
        }
    }

    // FORM INPUT CREDIT CARD EXPIRE
    function form_input_ccexpire ($fn)
    {
        // month
        $ret = '<select name="'.$fn.'[0]">'."\n";
        for ($i = 1; $i <= 12; $i++)
        {
            $ret .= '<option value="'.sprintf("%02d", $i).'">'.sprintf("%02d", $i).'</option>'."\n";
        }
        $ret .= '</select>'."\n";
        // year
        $ret .= '<select name="'.$fn.'[1]">'."\n";
        for($i = (strftime("%Y")); $i <= (strftime("%Y") + 10); $i++)
        {
            $ret .= '<option value="'.substr(sprintf("%04d", $i),2,2).'">'.$i.'</option>'."\n";
        }
        $ret .= '</select>'."\n";
        return $ret;
    }

    // PROC INPUT CREDIT CARD EXPIRE
    function proc_input_ccexpire ($fn)
    {
        $lfn = $GLOBALS["$fn"];
        return $lfn[0].$lfn[1];
    }

    // FORM INPUT CHECKBOX
    function form_input_checkbox ($name, $value = 1, $checked = 0)
    {
	    if ($checked == 1)
		    $str = '<input type="checkbox" name="'.$name.'" value="'.$value.'" checked>'."\n";
	    else
		    $str = '<input type="checkbox" name="'.$name.'" value="'.$value.'">'."\n";
	    return $str;	
    }

    // FORM INPUT RADIO
    function form_input_radio ($name, $value = 1, $checked = 0)
    {
	    if ($checked == 1)
		    $str = '<input type="radio" name="'.$name.'" value="'.$value.'" checked>'."\n";
	    else
		    $str = '<input type="radio" name="'.$name.'" value="'.$value.'">'."\n";
	    return $str;	
    }

    // FORM SUBMIT
    function form_submit ($value = "", $name = "submit", $extra = null)
    {
	    $str = '<input type="submit" class="button" name="'.$name.'" value="'.$value.'" '.$extra.'>'."\n";
	    return $str;
    }

    // FORM BUTTON
    function form_button ($value = "", $name = "submit", $extra = null)
    {
	    $str = '<input type="button" class="button" name="'.$name.'" value="'.$value.'" '.$extra.'>'."\n";
	    return $str;
    }

    // FORM JS BUTTON (button using javascript)
    function form_js_button ($url = null, $vars = null, $skip = "")
    {
	    global $PHP_SELF;
	    if (!$url)
	        $url = $PHP_SELF;
	    if ($vars)
	        $url .= "?".$this->build_urlarg($vars,$skip);
	    $str = '<input type=button class="button" value=" &lt;&lt; Back " name="jsback" '.
	    $str .= 'onClick="javascript:self.location=\''.$url.'\';">'."\n";
	    return $str;
    }

    // BACK LINK (simple back url)
    function back_link ($howmany = 1, $url = "")
    {
        if (!$url)
            $url = 'javascript:history.back('.$howmany.');';
        return $this->p('&nbsp;&nbsp; '.$this->ahref('&lt;&lt; Back',$url));
    }

    // ADD BR (replace \n with <br>)
    function add_br ($text = "")
    {
	    $text = ereg_replace("\n","<br>\n",$text);
	    return $text;
    }

    // FORMAT MSG (make an email, web readable, etc.)
    function format_msg ($text = "")
    {
        global $config;
	    $arr = explode("\n",$text);
	    while (list($c,$val) = each($arr))
	    {
            // line break at 80 chars
            if (strlen($arr[$c]) > 80)
                $arr[$c] = wordwrap($arr[$c], 80);        
		    // add urls
		    $arr[$c] = $this->urlify($arr[$c]);
            // add emoticons
		    $arr[$c] = $this->emoticon($arr[$c]);
		    // quote message text
		    if (ereg("^&gt; &gt; &gt; &gt;",$arr[$c]) or ereg("^&gt;&gt;&gt;&gt;",$arr[$c]))
			    $arr[$c] = "<font color=#000099>".$arr[$c]."</font>";            
		    else if (ereg("^&gt; &gt; &gt;",$arr[$c]) or ereg("^&gt;&gt;&gt;",$arr[$c]))
			    $arr[$c] = "<font color=#990000>".$arr[$c]."</font>";
		    else if (ereg("^&gt; &gt;",$arr[$c]) or ereg("^&gt;&gt;",$arr[$c]))
			    $arr[$c] = "<font color=#007777>".$arr[$c]."</font>";
		    else if (ereg("^&gt;",$arr[$c]))
			    $arr[$c] = "<font color=#660066>".$arr[$c]."</font>";
            // add bug urls
            if (eregi("bug( | #)[0-9]+", $arr[$c]))
            {
                $arr[$c] = eregi_replace("bug( | #)([0-9]+)",
                                        "<a href=\"".$config->bug_system."\\2\">bug \\2</a>",
                                        $arr[$c]);
            }
            // add ticket urls
            if (eregi("ticket( | #)[0-9]+", $arr[$c]))
            {
                $arr[$c] = eregi_replace("ticket( | #)([0-9]+)",
                                        "<a href=\"".$config->base_url."viewticket.php?ticket_id=\\2\">ticket \\2</a>",
                                        $arr[$c]);
            }                   
	    }
	    $text = implode("\n",$arr);
	    return $this->pre($text);
    }

    // URLIFY (search text and make urls linkable)
    function urlify ($text)
    {
        $text = htmlspecialchars($text);
        // web address
        $urlreg = "(([a-zA-Z]+://)([^\t\r\n ]+))";
        if (ereg($urlreg,$text))
        {
            $text = ereg_replace($urlreg, "<a href=\"\\1\">\\2\\3</a>", $text);
        }
        // fix mime email address ie =?ISO-8859-1?Q?John_Doe=E8?=
        $mimereg = "=\?([^?]+)\?(.)\?([^?]*)\?=";
        if (ereg($mimereg,$text))
        {
            $text = ereg_replace($mimereg, "\\3", $text);
        }
        // email address
        $emreg = "(.*) &lt;([a-zA-Z0-9_\.\+\/-]+)@([a-zA-Z0-9_\.-]+\.[a-zA-Z0-9]+)&gt;(.*)";
        if (ereg($emreg,$text))
        {    
    	    $text = ereg_replace($emreg, "<a href='mailto:\\2@\\3'>\\1</a>", $text);
        }
        else
        {
	        $emailreg = "([a-zA-Z0-9_\.\+\/-]+)@([a-zA-Z0-9_\.-]+\.[a-zA-Z0-9]+)";
	        if (ereg($emailreg,$text))
	        {
    			    $text = ereg_replace($emailreg, "<a href='mailto:\\1@\\2'>\\1@\\2</a>", $text);
	        }
        }
        return $text;
    }

    // EMOTICON (convert text with emoticons)
    function emoticon ($text)
    {
        $smiles = array(
                        "/:-\)/" => "smile.gif",
		                "/:-\(/" => "sad.gif",
		                "/B-\)/" => "cool.gif",
		                "/:-p/" => "razz.gif",
		                "/;-\)/" => "wink.gif"
		               );
        while (list($smile,$img) = each($smiles))
        {
    	    $path = $this->img("emoticon/".$img, "", $img);
    	    $text = preg_replace($smile,$path,$text);
        }
        return $text;
    }

    // SHORTEN (take a string, and limit its length)
    function shorten ($str = "", $max = 40)
    {
	    $str = ereg_replace("\n","", $str);
	    if (strlen($str) > $max)
	    {
		    $str = substr($str,0,$max);
		    $str .= "...";
	    }
	    return $str;
    }

    // BUILD URLARG (build a valid URL from a list of name/values)
    function build_urlarg ($vars, $skip = null)
    {
        $arr = array();
        while(list($key, $val) = each($vars))
        {
                if ($skip and gettype($skip) == "array")
                {
                    if (in_array($key, $skip))
                        continue;
                }
                else if ($skip and $key == $skip)
                {
                    continue;
                }
    
                if(is_array($val))
                {
                        while(list($idx, $value) = each($val))
                        {
                                //echo "Encoding $key / $value<br>";
                                $arr[] = rawurlencode($key."[]")."=".rawurlencode($value);
                        }
                }
                else
                {
                    $arr[] = $key."=".rawurlencode($val);
                }
        }
        return implode("&", $arr);
    }

    // GEN PASSWD (generate a password for user form, etc)
    function gen_passwd ($pass_len = 8)
    {
        $nps = "";
        mt_srand ((double) microtime() * 1000000);
        while (strlen($nps)<$pass_len)
        {
            $c = chr(mt_rand (0,255));
            if (eregi("[a-z0-9]", $c)) $nps = $nps.$c;
        }
        return ($nps);
    }

    // TEMPLATE (read a template file and fill in the variables)
    function template ($theme, $template, $vars = null, $noremovetags = 0, $cache = 0)
    {
        global $config;
        
        // load from cache if we have already loaded before
        if ($cache and isset($this->template_cache[$template]))
        {
            $in = $this->template_cache[$template];
        }
        else
        {
            switch ($theme)
            {    
                // load from local template repository
                case "local":          
        			if (file_exists($template.".template"))
                    	$in = join("",file($template.".template"));
                    break;
    
                // load base template
                case "base":
        			if (file_exists($this->_file_root."/templates/".$config->lang."/".$template.".template"))
                    	$in = join("",file($this->_file_root."/templates/".$config->lang."/".$template.".template"));
                    break;
     
                // load template from theme
                default:
                    if (file_exists($this->_file_root."/include/themes/".$theme."/".$template.".template"))
                        $in = join("",file($this->_file_root."/include/themes/".$theme."/".$template.".template"));                
            }
        }

        // oops not found, load 404 template
		if (!$in)
        {
            $this->in404 = 1;
            $in = '';
            if ($config->web_debug)
                $in = $this->p($theme."|".$template);
			$in .= join("",file($this->_file_root."/templates/404.template"));
        }
        
        // cache this template to save on i/o
        if ($cache)
        {
            $this->template_cache[$template] = $in;
        }
        
        // return the text with the vars replaced
		return $this->template_replace($in, $vars, $noremovetags);
    }
    
    // TEMPLATE_REPLACE (does the substitution for TEMPLATE)
    function template_replace ($in = "", $vars = array(), $noremovetags = 0)
    {
        global $config;
           
        // orginal in (avoid duplicate replacements)
        $orig = $in;
                
        // default vars
		$vars['root'] =& $this->find_root();
        $vars['self'] =& $_SERVER['PHP_SELF'];
        $vars['request_uri'] =& $_SERVER['REQUEST_URI'];
        $vars['base_url'] =& $GLOBALS['config']->base_url;
        $vars['snapshot_date'] = $config->snapshot_date;
        $vars['snapshot_date_rh'] = $config->snapshot_date_rh;

        // replace vars in template
        // NOTE: using preg_replace() breaks as it wants to interpret '$1' in $val
	    while (list($key,$val) = each($vars))
	    {
            if (preg_match('/\{\$'.$key.'\}/', $orig))
            {
                $in = str_replace('{$'.$key.'}', $val, $in);
            }
            if (preg_match('/\{lc\(\$'.$key.'\\)}/', $orig))
            {
                $in = str_replace('{lc($'.$key.')}', ereg_replace(' ','_',strtolower($val)), $in);
            }
	    }
        unset($key, $val);
        
        // remove all the unset vars
        if ($noremovetags == 0)
            $in = preg_replace('/\{\$[a-z0-9_]+\}/', '', $in);        
        
        // get page name from template
        if (preg_match('/<!--TITLE:\[([\w\s\-\&\'\;]+)\]-->/', $orig, $arr))
        {
            $in = preg_replace('/<!--TITLE:\[([\w\s\-\&\'\;]+)\]-->/', '', $in);
            $this->template_title = $arr[1];
            unset($arr);
        }
        
        // load nested templates the template
        if (preg_match('/<!--INCLUDE:\[[a-z_\/]+\]-->/', $orig))
        {
            preg_match_all('/<!--INCLUDE:\[([a-z_\/]+)\]-->/', $orig, $match);
            for ($i = 0; $i < count($match[0]); $i++)
            {
                $tmpl = $this->template("local", $match[1][$i]);
                $match[1][$i] = preg_replace('/\//', '\\/', $match[1][$i]);
                $in = str_replace('<!--INCLUDE:['.$match[1][$i].']-->', "$tmpl", $in);
                unset($tmpl);
            }
            unset($match, $i);
        }
        
        // load and exec plugins in the template
        if (preg_match('/<!--EXEC:\[[0-9a-z\._=;@\-\?\/\|]+\]-->/', $orig))
        {
            preg_match_all('/<!--EXEC:\[([0-9a-z\._=;@\-\?\/\|]+)\]-->/', $orig, $match);
            for ($i = 0; $i < count($match[0]); $i++)
            {
                $plugin = new plugin($match[1][$i], $this->_file_root);
                $in = str_replace('<!--EXEC:['.$match[1][$i].']-->', $plugin->get(), $in);
                unset($plugin);
            }
            unset($match, $i);
        }
        
        // return finished page
        unset($orig);
        return $in;
    }

    // HTTP HEADER (better header)
    function http_header ($title = "")
    {
	    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
	    header("Cache-Control: no-store, no-cache, must-revalidate");
	    header("Cache-Control: post-check=0, pre-check=0", false);
	    header("Pragma: no-cache"); 
    }

    // REDIRECT (simple httpd header redirect) 
    function redirect ($url = ".")
    {
	    header("Location: ".$url);
    }
// end html class
}

?>
