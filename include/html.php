<?php

/*
  WineHQ
  HTML class
  by Jeremy Newman <jnewman@codeweavers.com>
*/

class html 
{
    public $_file_root;
    public $_web_root;
    public $page = "";
    public $page_title;
    public $page_blurb;
    public $page_theme;
    public $page_style;
    public $meta_keywords;
    public $meta_description;
    public $meta_og = array();
    public $rss_link;
    public $css_links = array('styles');
    public $js_links = array('jquery','utils');
    public $nav_add;
    public $_error_mode = 0;
    public $_view_mode = "default";
    public $relative = 1;
    public $in404 = 0;
    public $template_cache = array();
    public $lang = "en";
    public $languages = array();

    // constructor
    function __construct ($file_root = '.')
    {
        // set the file root
        $this->_file_root =& $file_root;
        $this->_web_root =& $GLOBALS['config']->base_root;

        // set HTML document defaults
        $this->page_title = $GLOBALS['config']->site_name;
        $this->page_blurb = $GLOBALS['config']->site_name;
        $this->page_theme = $GLOBALS['config']->theme;
        $this->page_style = "content";

        // get the language to be displayed in templates
        $this->lang = $this->get_lang();

        // start body object
        $this->page = "";
    }

    // SHOWPAGE (display the current page)
    public function showpage ()
    {
        global $config;

        // debugging output
        $debug_log = "";
        if ($config->web_debug)
            $debug_log = $this->pre($GLOBALS['debug_log'], 'id="debug_log"');

        // 404 not found header
        if ($this->in404)
            header("HTTP/1.1 404 Not Found");

        // set meta tags
        $meta_keywords = "";
        $meta_description = "";
        if ($this->meta_keywords)
            $meta_keywords = $this->meta("keywords", $this->meta_keywords);
        if ($this->meta_description)
            $meta_description = $this->meta("description", $this->meta_description);

        // open graph tags
        $meta_og = "";
        if (empty($this->in403) and empty($this->in404) and isset($this->meta_og) and is_array($this->meta_og))
        {
            // defaults and fallbacks
            if (empty($this->meta_og['title']) and !empty($this->page_title))
                $this->meta_og['title'] =& $this->page_title;
            if (empty($this->mega_og['site_name']))
                $this->meta_og['site_name'] = $config->site_name;
            if (empty($this->meta_og['description']) and !empty($this->meta_description))
                $this->meta_og['description'] = $this->meta_description;
            if (empty($this->meta_og['type']))
                $this->meta_og['type'] = 'article';

            // loop and build meta_og string
            foreach ($this->meta_og as $og => $data)
            {
                if (is_array($data))
                {
                    foreach ($data as $in_data)
                    {
                        if (!empty($meta_og))
                            $meta_og .= "    ";
                        $meta_og .= "<meta property=\"og:{$og}\" content=\"{$in_data}\">\n";
                    }
                }
                else
                {
                    if (!empty($meta_og))
                        $meta_og .= "    ";
                    $meta_og .= "<meta property=\"og:{$og}\" content=\"{$data}\">\n";
                }
            }
        }

        // rss link
        if ($this->rss_link)
            $rss_link = '<link rel="alternate" title="'.$title.'RSS" href="'.$this->rss_link.'" type="application/rss+xml">';

        // css links
        $css_links = "";
        if (count($this->css_links))
        {
            $i = 0;
            foreach ($this->css_links as $css_link)
            {
                $css_file = "{$this->_file_root}/{$css_link}.css";
                if (file_exists($css_file))
                {
                    $mtime = filemtime($css_file);
                    $css_links .= ($i > 0 ? str_repeat(" ", 4) : '').
                                  "<link rel=\"stylesheet\" href=\"{$this->_web_root}/{$css_link}.css?v={$mtime}\" ".
                                  "type=\"text/css\" media=\"screen, print\">\n";
                    $i++;
                    unset($mtime);
                }
                unset($css_file);
            }
            unset($i);
        }

        // javascript links
        $js_links = "";
        if (count($this->js_links))
        {
            $i = 0;
            foreach ($this->js_links as $js_link)
            {
                $js_file = "{$this->_file_root}/{$js_link}.js";
                if (file_exists($js_file))
                {
                    $mtime = filemtime($js_file);
                    $js_links .= ($i > 0 ? str_repeat(" ", 4) : "").
                                "<script src=\"{$this->_web_root}/{$js_link}.js?v={$mtime}\" type=\"text/javascript\"></script>\n";
                    $i++;
                    unset($mtime, $extra);
                }
                unset($js_file);
            }
            unset($i);
        }

        // display page based on view mode
        switch ($this->_view_mode)
        {
            // plain text view
            case "text":
                $this->http_header("text/plain");
                echo $this->page;
                break;

            // print view
            case "print":
                $this->http_header("text/html");
                echo $this->template(
                                     $this->page_theme,
                                     "{$this->page_style}_print",
                                     array(
                                           'page_title'     => $this->page_title,
                                           'page_body'      => $this->page
                                          ),
                                     1
                                    );
                break;

            // regular view
            default:
                $this->http_header("text/html");
                echo $this->template(
                                     $this->page_theme,
                                     $this->page_style,
                                     array(
                                           'page_title'       => $this->page_title,
                                           'page_blurb'       => $this->page_blurb,
                                           'meta_keywords'    => &$meta_keywords,
                                           'meta_description' => &$meta_description,
                                           'meta_og'          => &$meta_og,
                                           'css_links'        => &$css_links,
                                           'js_links'         => &$js_links,
                                           'rss_link'         => &$rss_link,
                                           'page_body'        => $this->page,
                                           'copyright_year'   => date("Y", time()),
                                           'debug_log'        => $debug_log
                                          ),
                                     1
                                    );
        }

        // cleanup
        unset($debug_log);
    }

    // ERROR_PAGE
    public function error_page ($message = null)
    {
        global $config;
        // set error mode
        $this->_error_mode = 1;
        // create error page (overwrites the current page in progress)
        $this->page = $message;
        // show page
        $this->clear_buffer();
        $this->showpage($config->theme, "{$config->title} - Internal Error");
        exit();
    }

    // SHUTDOWN HANDLER (FATAL PHP errors)
    public function error_shutdown ()
    {
        // get information about last error
        $error = error_get_last();
        // handle error if defined
        if (!empty($error))
        {
            // output error
            $this->error_handler($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    // ERROR_HANDLER
    public function error_handler ($errno, $errstr, $errfile, $errline, $errcontext = null)
    {
        global $config;
        
        // don't exit on a notice
        if ($errno == E_NOTICE or $errno == E_WARNING)
            return;

        // PHP error codes
        $err = array(
                     1    => 'E_ERROR',
                     2    => 'E_WARNING',
                     4    => 'E_PARSE',
                     8    => 'E_NOTICE',
                     16   => 'E_CORE_ERROR',
                     32   => 'E_CORE_WARNING',
                     64   => 'E_COMPILE_ERROR',
                     128  => 'E_COMPILE_WARNING',
                     256  => 'E_USER_ERROR',
                     512  => 'E_USER_WARNING',
                     1024 => 'E_USER_NOTICE',
                     2047 => 'E_ALL',
                     2048 => 'E_STRICT',
                     4096 => 'E_RECOVERABLE_ERROR',
                     8192 => 'E_DEPRECATED'
                    );

        // write to the error log (move above the above return to get NOTICE and WARNING messages)
        if (isset($config->error_log) and file_exists($config->error_log))
        {
            error_log(
                      "[".date("D M j G:i:s Y",time())."] [".$err[$errno]."] ".$errfile.":".$errline." - ".$errstr."\n",
                      3,
                      $config->error_log
                     );
        }

        // show additional debug output
        if ($config->web_debug and function_exists('debug_backtrace') and !empty($errcontext))
        {
            // build context
            $ctx = '';
            foreach ($errcontext as $key => $value)
            {
                switch (gettype($value))
                {
                    case "string":
                        $ctx .= "[$key] => $value\n";
                        break;
                    default:
                        $ctx .= "[$key] => ".gettype($value)."\n";
                }
            }
            
            // build backtrace
            $backtrace = debug_backtrace();
            foreach ($backtrace as $bt)
            {
               $args = '';
               if (isset($bt['args']))
               {
                   foreach ($bt['args'] as $a)
                   {
                       if (!empty($args))
                           $args .= ', ';
                       switch (gettype($a))
                       {
                           case 'integer':
                           case 'double':
                               $args .= $a;
                               break;
                           case 'string':
                               $a = htmlspecialchars(substr($a, 0, 64)).((strlen($a) > 64) ? '...' : '');
                               $args .= "\"$a\"";
                               break;
                           case 'array':
                               $args .= 'Array('.count($a).')';
                               break;
                           case 'object':
                               $args .= 'Object('.get_class($a).')';
                               break;
                           case 'resource':
                               $args .= 'Resource('.strstr($a, '#').')';
                               break;
                           case 'boolean':
                               $args .= $a ? 'True' : 'False';
                               break;
                           case 'NULL':
                               $args .= 'Null';
                               break;
                           default:
                               $args .= 'Unknown';
                       }
                   }
               }
               $output .= "\n";
               $output .= "file: {$bt['file']}\n";
               $output .= "line: {$bt['line']}\n";
               $output .= "call: {$bt['class']}{$bt['type']}{$bt['function']}($args)\n";
            }
            
            $debug = "Context: \n".$ctx."\n\n";
            $debug .= "Backtrace: \n".$output."\n";
        }
        else
        {
            // cleanup path for non debug mode
            $errfile = basename($errfile);
        }
        
        // load into template and display error page
        $vars = array(
                      'errno'      => $data->err[$errno],
                      'errstr'     => $errstr,
                      'errfile'    => $errfile,
                      'errline'    => $errline,
                      'debug'      => $debug
                     );
        $this->error_page($this->template('local', 'global/fatal_error', $vars));
    }

    // GET LANG (get the language used for this session)
    public function get_lang ()
    {
        // default from config
        $lang = $GLOBALS['config']->lang;
        
        // load language from URL or cookie
        if (isset($_COOKIE['lang']) and in_array($_COOKIE['lang'], $GLOBALS['config']->languages))
        {
            debug("global", "lang from cookie: {$_COOKIE['lang']}");
            $lang = $_COOKIE['lang'];
            return $lang;
        }

        // load from web browser environment
        if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
        {
            $hal = preg_split("/\;/", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
            $avail = preg_split('/\,/', array_shift($hal));
            if (!empty($avail))
            {
                // loop through languages
                $avail = array_map("strtolower", $avail);
                foreach ($avail as $inLang)
                {
                    // check to see if langage is available
                    if (in_array($inLang, $GLOBALS['config']->languages))
                    {
                        debug("global", "lang from browser: {$inLang}");
                        return $inLang;
                    }

                    // check to see if variation of langage is available
                    if (strlen($inLang) > 2)
                    {
                        $inLangAlt = substr($inLang, 0, 2);
                        if (in_array($inLangAlt, $GLOBALS['config']->languages))
                        {
                            debug("global", "lang from browser: {$inLangAlt}");
                            return $inLangAlt;
                        }
                    }
                }
            }
            unset($hal, $avail);
        }

        // return default language
        debug("global", "lang default: {$lang}");
        return $lang;
    }


    // GET ROOT - get the root of the website
    public function get_root ()
    {
        return ($this->relative ? $this->_web_root : preg_replace("/\/$/", "", $GLOBALS['config']->base_url));
    }

    // HTML BR
    public function br ($count = 1)
    {
        return str_repeat("<br />", $count);
    }
    
    // HTML IMG Tag
    public function img ($src, $align = "", $alt = " ", $width = null, $height = null, $extra = null)
    {
        if ($align) $align = ' align="'.$align.'"';
        if ($alt) $alt = ' alt="'.$alt.'"';
        if ($extra) $extra = ' '.$extra;
        if ($src and is_file($this->_file_root."/images/".$src))
        {
            // load image from images dir
            $size = getimagesize($this->_file_root."/images/".$src);
            if ($size[3])
                $size = ' '.$size[3];
            return '<img src="'.$this->get_root()."/images/".$src.'"'.$size.$align.$extra.$alt.' />';
        }
        else if ($src)
        {
            // load other image
            if ($width) $width = ' width="'.$width.'"';
            if ($height) $height = ' height="'.$height.'"';
            return '<img src="'.$src.'"'.$width.$height.$align.$extra.$alt.' />';
        }
    }
    
    // HTML A HREF
    public function ahref ($label, $url = "", $extra = "")
    {
        if (!$label and !$url)
            return " &nbsp; ";
        if ($extra)
            $extra = " {$extra}";
        if (!$label and $url)
        {
            if (preg_match('/\@/', $url))
            {
                return "<a href=\"mailto:{$url}\"{$extra}>{$url}</a>";
            }
            else
            {
                return "<a href=\"{$url}\"{$extra}>".$this->shorten($url, 40)."</a>";
            }
        }
        else
        {
            return "<a href=\"{$url}\"{$extra}>{$label}</a>";
        }
    }

    // HTML B (bold)
    public function b ($str, $extra = null)
    {
        if ($extra) { $extra = " ".$extra; }
        return "<b".$extra.">".$str."</b>";
    }

    // HTML I (italics)
    public function i ($str, $extra = null)
    {
        if ($extra) { $extra = " ".$extra; }
        return "<i".$extra.">".$str."</i>";
    }

    // HTML Hn (header text)
    public function h ($n, $str, $extra = null)
    {
        if ($extra) { $extra = " ".$extra; }
        return "<H$n".$extra.">".$str."</H$n>";
    }

    // HTML BLOCKQOUTE (indent)
    public function blockquote ($str, $extra = null)
    {
        if ($extra) { $extra = " ".$extra; }
        return "<blockquote".$extra.">".$str."</blockquote>";
    }

    // HTML SMALL (small text)
    public function small ($str, $extra = null)
    {
        if ($extra) { $extra = " ".$extra; }
        return "<small".$extra.">".$str."</small>";
    }

    // HTML P
    public function p ($str = "&nbsp;", $extra = null)
    {
        if ($extra) { $extra = " ".$extra; }
        return "<p".$extra.">".$str."</p>";
    }

    // HTML DIV
    public function div ($str = "&nbsp;", $extra = null)
    {
        if ($extra) { $extra = " ".$extra; }
        return "<div".$extra.">$str</div>";
    }
    
    // HTML SPAN
    public function span ($str = "&nbsp;", $extra = null)
    {
        if ($extra) { $extra = " ".$extra; }
        return "<span".$extra.">".$str."</span>";
    }

    // HTML SUP
    public function sup ($str = "", $extra = null)
    {
        if ($extra) { $extra = " ".$extra; }
        return "<sup".$extra.">".$str."</sup>";
    }

    // HTML PRE
    public function pre ($str = "&nbsp;", $extra = null)
    {
        if ($extra) { $extra = " ".$extra; }
        return "<pre".$extra.">".$str."</pre>";
    }

    // HTML META
    public function meta ($name = "", $content = "")
    {
        if ($name)
            return '<meta name="'.$name.'" content="'.$content.'">';
        return "";
    }

    // HTML UL
    public function ul ($str = "", $extra = null)
    {
        if (is_array($str))
            $str = join("", array_map(array($this, "li"), $str));
        if ($extra) $extra = " {$extra}";
        return "<ul{$extra}>{$str}</ul>";
    }

    // HTML OL
    public function ol ($str = "", $extra = null)
    {
        if (is_array($str))
            $str = join("", array_map(array($this, "li"), $str));
        if ($extra) $extra = " {$extra}";
        return "<ol{$extra}>{$str}</ol>";
    }

    // HTML LI
    public function li ($str = "", $extra = null)
    {
        if ($extra) $extra = " {$extra}";
        return "<li{$extra}>{$str}</li>";
    }

    // encode text for use in a form field. Removes HTML reserved characters. (same as htmlspecialchars())
    public function encode ($str)
    {
        $str = str_replace('&', '&amp;', $str);
        $str = str_replace('\'', '&#039;', $str);
        $str = str_replace('"', '&quot;', $str);
        $str = str_replace('<', '&lt;', $str);
        $str = str_replace('>', '&gt;', $str);
        return $str;
    }

    // decode text encoded with the encode() function
    public function decode ($str)
    {
        $str = str_replace('&gt;', '>', $str);
        $str = str_replace('&lt;', '<', $str);
        $str = str_replace('&quot;', '\"', $str);
        $str = str_replace('&#039;', '\'', $str);
        $str = str_replace('&amp;', '&', $str);
        return $str;
    }
    
    // FORM START
    public function form_start ($script, $name, $method = "get", $extra = null)
    {
        $str = '<form name="'.$name.'" action="'.$script.'" method="'.strtolower($method).'" '.$extra.'>'."\n";
        return $str;
    }

    // FORM END
    public function form_end ()
    {
        return '</form>'."\n";
    }

    // FORM INPUT TEXT
    public function form_input_text ($name, $size = 20, $value = "", $max = 0, $extra = null)
    {
        if ($extra)
            $extra = $extra.' ';
        if ($max)
            $extra .= 'maxlength="'.$max.'" ';
        if ($size >= 100)
            $size = 'style="width:100%;" ';
        else
            $size = 'size="'.$size.'" ';
        $value = preg_replace('/\{\$root\}/', '&#123;&#036;root&#125;', $value);
        $str = '<input type="text" name="'.$name.'" '.$size.'value="'.$value.'" '.$extra.'/>'."\n";
        return $str;
    }

    // FORM INPUT PASSWORD
    public function form_input_password ($name, $size = 20, $value = "", $max = 0, $extra = null)
    {
        if ($max)
            $maxlengh = ' maxlength="'.$max.'"';
        if ($extra)
            $extra = ' '.$extra;
        $str = '<input type="password" name="'.$name.'" size="'.$size.'" value="'.$value.'"'.$maxlengh.$extra.' />'."\n";
        return $str;
    }

    // FORM INPUT HIDDEN FIELD
    public function form_input_hidden ($name, $value = "")
    {
        $str = '<input type="hidden" name="'.$name.'" value="'.$value.'" />'."\n";
        return $str;
    }

    // FORM INPUT TEXT AREA
    public function form_input_textarea ($name, $cols = 20, $rows = 5, $value = "")
    {
        $value = preg_replace('/\{\$root\}/', '&#123;&#036;root&#125;', $value);
        if ($cols >= 100)
            $cols = 'style="width:100%;" cols="72" ';
        else
            $cols = 'cols="'.$cols.'" ';
        $str = '<textarea name="'.$name.'" rows="'.$rows.'" '.$cols.'wrap="soft">'.$value.'</textarea>'."\n";
        return $str;
    }

    // FORM INPUT SELECT (drop down)
    public function form_input_select ($name, $options, $selected = "", $size = 1, $multi = null, $extra = "", $width = 72)
    {
        if ($multi)
            $multi = " multiple";
        if ($extra)
            $extra = " ".$extra;
        if (!$width)
            $width = 72;
        if (!is_array($options))
            return "";
        $str = '<select name="'.$name.'" size="'.$size.'"'.$multi.$extra.'>'."\n";
        while (list($key, $val) = each($options))
        {
            $val = $this->shorten($val, $width);
            if (is_array($selected) and in_array($key, $selected))
                $str .= '<option value="'.$key.'" selected="selected">'.$val.'</option>'."\n";
            else if ("$key" == "$selected")
                $str .= '<option value="'.$key.'" selected="selected">'.$val.'</option>'."\n";
            else
                $str .= '<option value="'.$key.'">'.$val.'</option>'."\n";		
        }
        $str .= '</select>'."\n";
        return $str;
    }

    // FORM INPUT TIMESTAMP (make an input field for timestamp)
    public function form_input_timestamp ($fn, $timestamp, $nohourmin = 0, $allow_null = 0)
    {
        $months = array(1 => 'January', 'February', 'March', 'April', 'May',
                      'June', 'July', 'August', 'September', 'October',
                      'November', 'December');          
        // month
        $ascvar = $fn . "[1]";
        $ret.='  <select name="'.$ascvar.'">'."\n";
        if ($allow_null)
            $ret.='    <option value=""></option>'."\n";
        $cur = 1;
        while ($cur <= 12)
        {
            if ($timestamp and date("n",$timestamp) == $cur) { $selected = ' selected="selected"'; }
            $ret.='    <option value="'.$cur.'"'.$selected.'>'.$months[$cur].'</option>'."\n";
            $cur++;
            $selected = "";
        }
        $ret.='  </select>'."\n";
        
        // day
        $ascvar = $fn . "[2]";
        $ret.='  <select name="'.$ascvar.'">'."\n";
        if ($allow_null)
            $ret.='    <option value=""></option>'."\n";
        $cur = 1;
        while ($cur <= 31)
        {
            if ($timestamp and date("j",$timestamp) == $cur) { $selected = ' selected="selected"'; }
            $ret.='    <option value="'.$cur.'"'.$selected.'>'.$cur.'</option>'."\n";
            $cur++;
            $selected = "";
        }
        $ret.='  </select>'."\n";
        
        // year
        $ascvar = $fn . "[0]";
        $ret.='  <select name="'.$ascvar.'">'."\n";
        if ($allow_null)
            $ret.='    <option value=""></option>'."\n";
        $cur = "1998";
        while ($cur <= (date("Y")+5))
        {
            if ($timestamp and date("Y",$timestamp) == $cur) { $selected = ' selected="selected"'; }
            $ret.='    <option value="'.$cur.'"'.$selected.'>'.$cur.'</option>'."\n";
            $cur++;
            $selected = "";
        }
        $ret.='  </select>'."\n";
        
        // toggle hour minute display
        if ($nohourmin == 0)
        {
            // hour
            $ascvar = $fn . "[3]";
            $ret.='  <select name="'.$ascvar.'">'."\n";
            if ($allow_null)
                $ret.='    <option value=""></option>'."\n";
            $cur = "0";
            while ($cur <= "23")
            {
                if ($timestamp and date("G",$timestamp) == $cur) { $selected = ' selected="selected"'; }
                $ret.='    <option value="'.$cur.'"'.$selected.'>'.$cur.'</option>'."\n";
                $cur++;
                $selected = "";
            }
            $ret.='  </select>'."\n";
            
            // min
            $ascvar = $fn . "[4]";
            $ret.='  <select name="'.$ascvar.'">'."\n";
            if ($allow_null)
                $ret.='    <option value=""></option>'."\n";
            $cur = "0";
            while ($cur <= "59")
            {
                if ($cur < "10") { $cur = "0".$cur; }
                if ($timestamp and date("i",$timestamp) == $cur) { $selected = ' selected="selected"'; }
                $ret.='    <option value="'.$cur.'"'.$selected.'>'.$cur.'</option>'."\n";
                $cur++;
                $selected = "";
            }
            $ret.='  </select>'."\n";
        }
        else if ($timestamp)
        {
            // default to midnight
            $ret .= '<input type="hidden" name="'.$fn.'[3]" value="'.date("G",$timestamp).'" />'."\n";
            $ret .= '<input type="hidden" name="'.$fn.'[4]" value="'.date("i",$timestamp).'" />'."\n";
        }
        
        return $ret;
    }

    // PROC INPUT TIMESTAMP (process timestamp field)
    public function proc_input_timestamp ($fn, $mode = 'unix')
    {
        if (is_array($fn))
            $lfn =& $fn;
        else
            $lfn =& $_REQUEST["$fn"];
        $strdate = $lfn[0]."-".$lfn[1]."-".$lfn[2];
        if ($lfn[3] and $lfn[4])
            $strdate .= " ".$lfn[3].":".$lfn[4];
        if ($strdate != "--")
        {
            $timestamp = strtotime($strdate);
            switch ($mode)
            {
                // return sql timestamp
                case "sql":
                    return date("YmdHis",$timestamp);
                    break;
                    
                // return unix timestamp
                default:
                    return $timestamp;
            }
        }
        else
        {
            return -1;
        }
    }

    // FORM INPUT CHECKBOX
    public function form_input_checkbox ($name, $value = 1, $checked = 0, $extra = null)
    {
        if (!$extra)
            $extra = 'class="checkbox"';
        $extra = " $extra";
        if ($checked == 1)
            $str = '<input type="checkbox" name="'.$name.'" value="'.$value.'" checked="checked"'.$extra.' />'."\n";
        else
            $str = '<input type="checkbox" name="'.$name.'" value="'.$value.'"'.$extra.' />'."\n";
        return $str;
    }

    // FORM INPUT RADIO
    public function form_input_radio ($name, $value = 1, $checked = 0, $extra = null)
    {
        if (!$extra)
            $extra = 'class="radio"';
        if ($extra)
            $extra = " $extra";
        if ($checked == 1)
            $str = '<input type="radio" name="'.$name.'" value="'.$value.'" checked="checked"'.$extra.' />'."\n";
        else
            $str = '<input type="radio" name="'.$name.'" value="'.$value.'"'.$extra.' />'."\n";
        return $str;
    }

    // FORM MULTI CHECKBOX (multi select using checkboxes [tableEditor set field]) 
    public function form_multi_checkbox ($name, $options, $selected = "")
    {
        if (!is_array($options))
            return "";
        while (list($key, $val) = each($options))
        {
            if (is_array($selected) and in_array($key, $selected))
                $str .= $this->form_input_checkbox($name, $key, 1)." ".$val.$this->br();
            else if ($key == $selected)
                $str .= $this->form_input_checkbox($name, $key, 1)." ".$val.$this->br();
            else
                $str .= $this->form_input_checkbox($name, $key)." ".$val.$this->br();
        }
        return $str;
    }

    // FORM INPUT MULTI RADIO (multi select using radio [tableEditor enum field]) 
    public function form_multi_radio ($name, $options, $selected = "")
    {
        if (!is_array($options))
            return "";
        while (list($key, $val) = each($options))
        {
            if ($key == $selected)
                $str .= $this->form_input_radio($name, $key, 1)." ".$val.$this->br();
            else
                $str .= $this->form_input_radio($name, $key)." ".$val.$this->br();
        }
        return $str;
    }

    // FORM FILE BUTTON (needs enctype set on form)
    public function form_input_file ($name, $value = "")
    {
        return '<input type="file" name="'.$name.'" value="'.$value.'" />'."\n";
    }

    // FORM SUBMIT
    public function form_submit ($value = "", $name = "", $extra = null)
    {
        if ($name)
            $name = ' name="'.$name.'"';
        $str = '<input type="submit"'.$name.' value="'.$value.'" class="button" '.$extra.' />'."\n";
        return $str;
    }

    // FORM BUTTON
    public function form_button ($value = "", $name = "", $extra = null)
    {
        if ($name)
            $name = ' name="'.$name.'"';
        if ($extra)
            $extra .= " ";
        return "<input type=\"button\" class=\"button\"{$name} value=\"{$value}\" {$extra}/>\n";
    }

    // FORM JS BUTTON (button using javascript)
    public function form_js_button ($url = null, $name = "&lt;&lt; Back", $extra = "")
    {
        if (!$url)
            $url = $_SERVER['HTTP_REFERER'];
        if ($extra)
            $extra .= " ";
        return "<input type=\"button\" class=\"button\" value=\"{$name}\" ".
               "onClick=\"javascript:self.location='$url';\" {$extra}/>\n";
        return $str;
    }

    // BACK LINK (simple back url)
    public function back_link ($howmany = 1, $url = "")
    {
        if (!$url)
            $url = 'javascript:history.back('.$howmany.');';
        return $this->p('&nbsp;&nbsp; '.$this->ahref('&lt;&lt; Back',$url));
    }

    // ADD BR (replace \n with <br>)
    public static function add_br ($text = "")
    {
        $text = preg_replace("/\n/", "<br>\n", $text);
        return $text;
    }
    
    // HTML2TXT (convert HTML to text format)
    public function html2txt ($str)
    {
        // remove HEADER junk, CSS, JS, and CDATA
        $str = preg_replace("'<head[^>]*>.*</head>'siU", '', $str);
        $str = preg_replace("'<style[^>]*>.*</style>'siU", '', $str);
        $str = preg_replace("'<script[^>]*>.*</script>'siU", '', $str);
        $str = preg_replace('@<![\s\S]*?--[ \t\n\r]*>@', '', $str);
        // P with closing tags replacement
        $str = preg_replace("'<p[^>]*>(.*)</p>'siU", "\\1\n\n", $str);
        // replace BR and single P tags
        $str = preg_replace('/<br\s*\/?>/i', "\n", $str);
        $str = preg_replace('/<p\s*\/?>/i', "\n\n", $str);
        // strip all other tags
        $str = preg_replace('@<[\/\!]*?[^<>]*?>@si', '', $str);
        // convert entities
        $str = preg_replace("/&nbsp;/", " ", $str);
        $str = html_entity_decode($str, null, "UTF-8");
        // remove extra whitespace
        $str = preg_replace("/\t/", "    ", $str);
        $str = preg_replace("/\r/", "", $str);
        $str = preg_replace("/ {1,}\n/sU", "\n", $str);
        $str = preg_replace("/\n{3,}/sU", "\n\n", $str);
        $str = preg_replace("/\n{4,}/sU", "", $str);
        // return text
        return $str;
    }
    
    // FORMAT MSG (make an email, web readable, etc.)
    public function format_msg ($text = "", $class = "", $pre = 0)
    {
        $arr = explode("\n", $text);
        while (list($c,$val) = each($arr))
        {
            // remove any existing carrige returns
            $arr[$c] = preg_replace("/[\n\r]/", "", $arr[$c]);
            // line break at 80 chars (only for pre)
            if ($pre and strlen($arr[$c]) > 80)
            {
                $arr[$c] = wordwrap($arr[$c], 80, "\n", 1);
            }
            else if (!$pre)
            {
                // if a single word is longer than 72 chars, add spaces to it (for non-pre)
                $arr[$c] = $this->wraplongwords($arr[$c]);
            }
            // remove any remaining special characters
            $arr[$c] = $this->encode($arr[$c]);
            // add urls
            $arr[$c] = $this->urlify($arr[$c]);
            // add emails
            $arr[$c] = $this->emailify($arr[$c]);
            // add emoticons
            $arr[$c] = $this->emoticon($arr[$c]);
            // strip slashes (causes crash if not here)
            $arr[$c] = preg_replace('/\\\/', '&#092;', $arr[$c]);
            // quote message text
            if ($class)
            {
                if (preg_match('/^&gt;\s?&gt;\s?&gt;\s?&gt;/', $arr[$c]))
                    $arr[$c] = $this->span($arr[$c], "class=\"{$class}d\"");
                else if (preg_match('/^&gt;\s?&gt;\s?&gt;/', $arr[$c]))
                    $arr[$c] = $this->span($arr[$c], "class=\"{$class}c\"");
                else if (preg_match('/^&gt;\s?&gt;/', $arr[$c]))
                    $arr[$c] = $this->span($arr[$c], "class=\"{$class}b\"");
                else if (preg_match('/^&gt;/', $arr[$c]))
                    $arr[$c] = $this->span($arr[$c], "class=\"{$class}a\"");
            }
        }
        $text = implode("\n", $arr);
        
        if ($pre)
            if ($class)
                return $this->pre($text, 'class="'.$class.'"');
            else
                return $this->pre($text);
        else
            if ($class)
                return $this->span(nl2br($text), 'class="'.$class.'"');
            else
                return nl2br($text);
    }

    // WRAPLONGWORDS (fix text that would be too long for web viewing)
    public function wraplongwords ($text, $len = 72, $skip_pre = false)
    {
        $skip_wrap = 0;
        $lines = preg_split('/\n/', $text);
        for ($l = 0; $l < count($lines); $l++)
        {
            $words = preg_split('/\s/', $lines[$l]);
            for ($x = 0; $x < count($words); $x++)
            {
                if ($skip_pre)
                {
                    switch (true)
                    {
                        case preg_match("/\<pre\>/", $words[$x]):
                            $skip_wrap = 1;
                            break;
                        case preg_match("/\<\/pre\>/", $words[$x]):
                            $skip_wrap = 0;
                            break;
                    }
                }
                if (strlen($words[$x]) >= $len and !preg_match("%(http://|https://|ftp://)%", $words[$x]) and !$skip_wrap)
                {
                    $arr = preg_split('//', $words[$x], -1, PREG_SPLIT_NO_EMPTY);
                    for ($c = 0; $c < count($arr); $c = $c + $len)
                    {
                        array_splice($arr, $c, 0, 0);
                        $arr[$c] = " ";
                    }
                    $words[$x] = join("", $arr);
                    unset($arr);
                }
            }
            $lines[$l] = join(" ", $words);
        }
        return join("\n", $lines);
    }

    // JSENCODE (convert a string for use in a JavaScript function)
    public function jsencode ($str)
    {
        $str = preg_replace('/\r/', '', $str);
        $str = preg_replace('/\n/', '\\n', $str);
        $str = preg_replace('/\'/', '\\\'', $str);
        return $str;
    }

    // URLIFY (search text and make urls linkable, also wrap URL at 100 chars)
    public function urlify ($text)
    {
        // only use if text has links in it
        if (preg_match('%(http|https|ftp|rss)(://)([-\w\.]+)%', $text))
        {
            // extract existing HTML so it is left unprocessed (for example, bbcode is pre-converted)
            $html_matches = array();
            preg_match_all("/(<a href=.*>.*<\/a>)/Us", $text, $matches, PREG_PATTERN_ORDER);
            foreach ($matches[0] as $c => $match)
            {
                $html_matches[$c] = $matches[1][$c];
                $text = str_replace("$match", "__HTMLMATCH_{$c}__", $text);
            }
            
            // fix URL converted vars temporarily
            $text = preg_replace('/&gt;/', '<-RPLME->', $text);
            $text = preg_replace('/&quot;/', '"-RPLME-"', $text);
            
            // add a space to the beginning text so regex will work
            $text = " $text";
            
            // perform regular expression
            $text = preg_replace(
                                 '%((http|https|ftp|rss)(://)([-\w\.]+)(:\d+)?(/)?([\w/_\-\.\*\~]+)?(\?)?([\w_\-\.\;\&\=\%]+)?(\#)?([-\w\.]+)?)%e',
                                 "'<a href=\"\\1\">'.wordwrap('\\1', 100, '\n', 1).'</a>'",
                                 $text
                                );
                                
            // remove our temp conversion
            $text = preg_replace('/<-RPLME->/', '&gt;', $text);
            $text = preg_replace('/"-RPLME-"/', '&quot;', $text);
            
            // remove our temp space
            $text = preg_replace('%^ %', '', $text);
            
            // re-insert HTML
            preg_match_all("/(__HTMLMATCH_(\d+)__)/", $text, $matches, PREG_PATTERN_ORDER);
            foreach ($matches[0] as $c => $match)
            {
                $text = str_replace("$match", $html_matches[$c], $text);
            }
        }
        // return linkify'd text
        return $text;
    }

    // EMAILIFY (convert email addresses to links)
    public function emailify ($text, $convert = 0)
    {
        // fix quoted printable
        if (preg_match('/^\=/', $text))
        {
            $text = quoted_printable_decode($text);
            $text = mb_convert_encoding($text, 'UTF-8');
            $text = preg_replace('/^\=\?[a-z0-9\-]+\?Q\?(.*)\?\=/', "\\1", $text);
        }
        $emreg = '/(.*) (\<|\&lt\;)([a-zA-Z0-9_\.\+\/-]+)@([a-zA-Z0-9_\.-]+\.[a-zA-Z0-9]+)(\>|\&gt\;)(.*)/';
        if ($convert and preg_match($emreg, $text))
        {
            // long format puts the name in and hides the email in the href
            $text = preg_replace($emreg."e", "'<a href=\"mailto:\\3@\\4\">'.wordwrap('\\1', 25, ' ', 1).'</a>'", $text);
        }
        else
        {
            // regular emails
            $emailreg = '/([a-zA-Z0-9_\.\+\/-]+)@([a-zA-Z0-9_\.-]+\.[a-zA-Z0-9]+)/';
            if (preg_match($emailreg, $text))
            {
                    $text = preg_replace($emailreg, "<a href=\"mailto:\\1@\\2\">\\1@\\2</a>", $text);
            }
        }
        return $text;
    }

    // EMOTICON (convert text with emoticons)
    public function emoticon ($text)
    {
        $smiles = array(
                        ":-)" => "smile.gif",
                        ":-(" => "sad.gif",
                        "B-)" => "cool.gif",
                        ":-p" => "razz.gif",
                        ";-)" => "wink.gif",
                        ":-D" => "biggrin.gif",
                        "X-(" => "mad.gif"
                       );
        while (list($smile,$img) = each($smiles))
        {
            $path = $this->img("emoticon/".$img);
            $text = str_replace($smile, $path, $text);
        }
        return $text;
    }

    // SHORTEN (take a string, and limit its length [multibyte compatible])
    public static function shorten ($str = "", $max = 40)
    {
        $str = preg_replace('/\n/', "", $str);
        if (mb_strlen($str) > $max)
        {
            $str = mb_substr($str, 0, $max);
            $str .= "...";
        }
        return $str;
    }

    // BUILD URLARG (build a valid URL from a list of name/values)
    public function build_urlarg ($vars, $skip = null, $array = null)
    {
        // if not a list, try converting it into a list
        if (!is_array($vars))
        {
            list($url,$params) = preg_split('/\?/', $vars, 2);
            if (preg_match('/;/', $params))
            {
                $urls = preg_split('/;/',$params);
                $vars = array();
                foreach ($urls as $pair)
                {
                    list($key,$value) = preg_split('/=/',$pair);
                    $vars[$key] = $value;
                }
            }
            else
            {
                // return nothing, as there is no vars
                return;
            }
        }
        
        // loop through vars and remove any we do not want, then build url
        $arr = array();
        while(list($key, $val) = each($vars))
        {
            // skip COOKIE vars
            if (isset($_COOKIE[$key]))
               continue;
            
            if ($skip and gettype($skip) == "array")
            {
                // simple in array check for strings
                if (in_array($key, $skip))
                    continue;
                // support for nested array keys
                foreach ($skip as $skip_ar)
                {
                    // if skip is an array and the array is the correct key check values of skip_ar
                    if (is_array($skip_ar) and isset($skip_ar[$key]))
                    {
                        $val_keys = array_keys($val);
                        $skip_in = array_values($skip_ar);
                        if (in_array($skip_in[0], $val_keys))
                        {
                            continue 2;
                        }
                    }
                }
                
            }
            else if ($skip and $key == $skip)
            {
                // non arrays, just match key to skip value
                continue;
            }
            
            if ($array)
                $key = $array."[".$key."]";
            
            if(is_array($val))
            {
                    while(list($idx, $value) = each($val))
                    {
                            //echo "Encoding $key / $value<br>";
                            $arr[] = rawurlencode($key."[".$idx."]")."=".rawurlencode($value);
                    }
            }
            else
            {
                $arr[] = $key."=".rawurlencode($val);
            }
        }
        return implode(";", $arr);
    }

    // GEN PASSWD (generate a password for user form, etc)
    public function gen_passwd ($pass_len = 8)
    {
        $nps = "";
        mt_srand ((double) microtime() * 1000000);
        while (strlen($nps)<$pass_len)
        {
            $c = chr(mt_rand (0,255));
            if (in_array(strtolower($c), array('0', 'o' ,'i', 'l')))
                continue;
            if (preg_match("/[a-z0-9]/i", $c)) $nps = $nps.$c;
        }
        return ($nps);
    }

    // PERM LINK
    public function gen_perm_link ($str)
    {
        $str = preg_replace('/\s/', '12WSPC21', $str);
        $str = preg_replace('/\W/', '12WSPC21', $str);
        $str = preg_replace('/12WSPC21/', '-', $str);
        $str = preg_replace('/-{2,}/', '-', $str);
        $str = strtolower($str);
        return $str;
    }

    // TEMPLATE (read a template file and fill in the variables)
    public function template ($theme = null, $template, $vars = null, $noremovetags = 0)
    {
        global $config;
        
        // if no theme, use default theme from config
        if (!$theme)
            $theme = $config->theme;
        
        // debug
        debug("template", "loading template: theme:[{$theme}] lang: [{$this->lang}] template:[{$template}]");
        
        // load from cache if we have already loaded before
        if (isset($this->template_cache[$template]))
        {
            $in = $this->template_cache[$template];
        }
        else
        {
            // theme determines where template is loaded from
            switch ($theme)
            {
                // load from local template repository
                case "base":
                case "local":
                    if (file_exists("{$this->_file_root}/templates/{$this->lang}/{$template}.template"))
                        $in = join("",file("{$this->_file_root}/templates/{$this->lang}/{$template}.template"));
                    else if (($config->lang != $this->lang) and file_exists("{$this->_file_root}/templates/{$config->lang}/{$template}.template"))
                        $in = join("",file("{$this->_file_root}/templates/{$config->lang}/{$template}.template"));
                    break;
                
                // load from global
                case "global":
                    if (file_exists("{$this->_file_root}/templates/global/{$template}.template"))
                        $in = join("",file("{$this->_file_root}/templates/global/{$template}.template"));
                    break;

                // load template from theme
                default:
                    if (file_exists("{$this->_file_root}/templates/{$this->lang}/global/themes/{$theme}/{$template}.template"))
                        $in = join("",file("{$this->_file_root}/templates/{$this->lang}/global/themes/{$theme}/{$template}.template"));
                    else if (($config->lang != $this->lang) and file_exists("{$this->_file_root}/templates/{$config->lang}/global/themes/{$theme}/{$template}.template"))
                        $in = join("",file("{$this->_file_root}/templates/{$config->lang}/global/themes/{$theme}/{$template}.template"));              
            }
        }

        // oops not found, load 404 template
        if (!$in)
        {
            $in = '';
            $this->in404 = 1;
            if (file_exists("{$this->_file_root}/templates/{$this->lang}/global/404.template"))
                $in .= join("",file("{$this->_file_root}/templates/{$this->lang}/global/404.template"));
            else if (file_exists("{$this->_file_root}/templates/{$config->lang}/global/404.template"))
                $in .= join("",file("{$this->_file_root}/templates/{$config->lang}/global/404.template"));
            else
                $in .= $this->h1("404 Not Found!");
        }

        // cache this template to save on i/o
        $this->template_cache[$template] = $in;
        
        // return the text with the vars replaced
        return $this->template_replace($in, $vars, $noremovetags);
    }
    
    // TEMPLATE_REPLACE (does the substitution for TEMPLATE)
    public function template_replace ($in = "", $vars = array(), $noremovetags = 0)
    {
        // original in (avoid duplicate replacements)
        $orig = $in;
        
        // default path var
        $vars['root'] =& $this->_web_root;
        $vars['self'] =& $_SERVER['PHP_SELF'];
        $vars['file_root'] = "file://{$this->_file_root}";
        $vars['request_uri'] =& $_SERVER['REQUEST_URI'];
        $vars['request_delim'] = ($_GET ? ';' : '?');
        $vars['server_name'] =& $_SERVER['SERVER_NAME'];
        $vars['base_url'] =& $GLOBALS['config']->base_url;
        $vars['curtime_year'] = date('Y', time());
        
        // language vars
        if (defined("PAGE"))
        {
            $vars['langCode'] =& $this->lang;
            if (PAGE == "home" or PAGE == "lang")
            {
                $vars['langCur'] =& $GLOBALS['data']->languages[$this->lang]['name'];
                $vars['langChange'] =& $GLOBALS['data']->languages[$this->lang]['change'];
            }
        }
        
        // add config vars
        while (list($key, $val) = each($GLOBALS['config']))
        {
            if (is_string($val))
                $vars["config_{$key}"] = $val;
        }
        
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
                $in = str_replace('{lc($'.$key.')}', preg_replace('/ /','_',strtolower($val)), $in);
            }
            if (preg_match('/\{htmlspecialchars\(\$'.$key.'\\)}/', $orig))
            {
                $in = str_replace('{htmlspecialchars($'.$key.')}', htmlspecialchars($val), $in);
            }
        }
        unset($key, $val);
        
        // resave the new orig with the template vars inserted (this allows EXEC other statements to include vars)
        $orig = $in;
        
        // allow the insertion of GET FORM vars
        if (preg_match('/\{\$_GET\[[a-z0-9_]+\]\}/', $orig))
        {
            preg_match_all('/\{\$_GET\[([a-z0-9_]+)\]\}/', $orig, $match);
            for ($i = 0; $i < count($match[0]); $i++)
            {
                if (isset($_GET[$match[1][$i]]))
                    $in = str_replace('{$_GET['.$match[1][$i].']}', htmlspecialchars($_GET[$match[1][$i]]), $in);
                else
                    $in = str_replace('{$_GET['.$match[1][$i].']}', "", $in);
            }
            unset($match, $i);
        }
        
        // remove all the unset vars
        if ($noremovetags == 0)
            $in = preg_replace('/\{\$[a-z0-9_\-]+\}/', '', $in);        
        
        // get page name from template
        if (preg_match('/<!--TITLE:\[(.+)\]-->/', $orig, $arr))
        {
            debug("global", "adding to title: {$arr[1]}");
            $in = preg_replace('/<!--TITLE:\[(.+)\]-->\n/', '', $in);
            $this->page_title .= " - {$arr[1]}";
            unset($arr);
        }

        // override the page theme
        if (preg_match('/<!--THEME:\[([\w]+)\]-->/', $orig, $arr))
        {
            debug("global", "changing theme: {$arr[1]}");
            $in = preg_replace('/<!--THEME:\[([\w]+)\]-->\n/', '', $in);
            $this->page_theme = $arr[1];
            unset($arr);
        }

        // override the page style
        if (preg_match('/<!--STYLE:\[([\w]+)\]-->/', $orig, $arr))
        {
            debug("global", "changing page style: {$arr[1]}");
            $in = preg_replace('/<!--STYLE:\[([\w]+)\]-->\n/', '', $in);
            $this->page_style = $arr[1];
            unset($arr);
        }

        // get page blurb template
        if (preg_match('/<!--BLURB:\[(.+)\]-->/', $orig, $arr))
        {
            debug("global", "setting page blurb: {$arr[1]}");
            $in = preg_replace('/<!--BLURB:\[(.+)\]-->\n/', '', $in);
            $this->page_blurb = $arr[1];
            unset($arr);
        }

        // set the meta keywords used for a page
        if (preg_match('/<!--META_KEYWORDS:\[([\w\s\-\&\'\;\,]+)\]-->/', $orig, $arr))
        {
            $in = preg_replace('/<!--META_KEYWORDS:\[([\w\s\-\&\'\;\,]+)\]-->\n/', '', $in);
            $this->meta_keywords = $arr[1];
            unset($arr);
        }

        // set the meta description uses for a page
        if (preg_match('/<!--META_DESCRIPTION:\[([\w\s\-\&\'\;\,\.\?]+)\]-->/', $orig, $arr))
        {
            $in = preg_replace('/<!--META_DESCRIPTION:\[([\w\s\-\&\'\;\,\.\?]+)\]-->\n/', '', $in);
            $this->meta_description = $arr[1];
            unset($arr);
        }

        // set the meta og
        if (preg_match('/<!--META_OG:\[(.+)\|(.+)\]-->/', $orig))
        {
            preg_match_all('/<!--META_OG:\[(.+)\|(.+)\]-->/', $orig, $match);
            for ($i = 0; $i < count($match[0]); $i++)
            {
                if ($match[1][$i] == "image")
                {
                    if (empty($this->meta_og['image']))
                        $this->meta_og['image'] = array();
                    array_push($this->meta_og['image'], $match[2][$i]);
                }
                else
                {
                    $this->meta_og[$match[1][$i]] = $match[2][$i];
                }
                $in = str_replace('<!--META_OG:['.$match[1][$i].'|'.$match[2][$i].']-->', "", $in);
            }
        }

        // load nested templates the template
        if (preg_match('/<!--INCLUDE:\[[a-z0-9_\-\/]+\]-->/', $orig))
        {
            preg_match_all('/<!--INCLUDE:\[([a-z0-9_\-\/]+)\]-->/', $orig, $match);
            for ($i = 0; $i < count($match[0]); $i++)
            {
                $tmpl = $this->template("local", $match[1][$i]);
                $in = str_replace('<!--INCLUDE:['.$match[1][$i].']-->', "$tmpl", $in);
                unset($tmpl);
            }
            unset($match, $i);
        }

        // import CSS
        if (preg_match('/<!--CSS:\[.+\]-->/', $orig))
        {
            preg_match_all('/<!--CSS:\[(.+)\]-->/', $orig, $match);
            for ($i = 0; $i < count($match[0]); $i++)
            {
                $this->css_links[] = $match[1][$i];
                $in = str_replace('<!--CSS:['.$match[1][$i].']-->', "", $in);
            }
            unset($match, $i);
        }

        // import js
        if (preg_match('/<!--JS:\[.+\]-->/', $orig))
        {
            preg_match_all('/<!--JS:\[(.+)\]-->/', $orig, $match);
            for ($i = 0; $i < count($match[0]); $i++)
            {
                if ($this->_view_mode == "ajax")
                {
                    // in ajax mode, add the js link inline
                    $js = "";
                    $js_file = "{$this->_file_root}/{$match[1][$i]}.js";
                    if (file_exists($js_file))
                    {
                        $mtime = filemtime($js_file);
                        $js = "<script src=\"{$this->_web_root}/{$match[1][$i]}.js?v={$mtime}\" type=\"text/javascript\"></script>\n";
                        unset($mtime);
                    }
                    $in = str_replace('<!--JS:['.$match[1][$i].']-->', $js, $in);
                    unset($js, $js_file);
                }
                else
                {
                    // in other views, add js link to the page header
                    $this->js_links[] = $match[1][$i];
                    $in = str_replace('<!--JS:['.$match[1][$i].']-->', "", $in);
                }
            }
            unset($match, $i);
        }

        // load and exec plugins in the template
        if (preg_match('/<!--EXEC:\[.+\]-->/', $orig))
        {
            preg_match_all('/<!--EXEC:\[(.+)\]-->/', $orig, $match);
            for ($i = 0; $i < count($match[0]); $i++)
            {
                // module name
                $_module = $match[1][$i];
                
                // split params from module name
                $_PLUGIN = array();
                if (preg_match('/\?/', $_module))
                {
                    $arr = preg_split('/\?/', $_module, 2);
                    $_module = $arr[0];
                    $vars = preg_split("/\;/", $arr[1]);
                    foreach ($vars as $var)
                    {
                        list($a, $b) = preg_split('/\=/', $var, 2);
                        $_PLUGIN[$a] = $b;
                    }
                    unset($arr, $vars, $var, $a, $b);
                }

                // load plugin
                $out = include_plugin($_module, $_PLUGIN);
                if ($out === false)
                    $out = $this->span("plugin:[{$_module}] not found!", 'class="huge error"');
                $in = str_replace('<!--EXEC:['.$match[1][$i].']-->', $out, $in);
                unset($plugin, $out);
            }
            unset($match, $i);
        }

        // override the page view mode (print, text)
        if (preg_match('/<!--VIEW:\[([\w]+)\]-->/', $orig, $arr))
        {
            $this->_view_mode = strtolower($arr[1]);
            $in = preg_replace('/<!--VIEW:\[([\w]+)\]-->\n/', '', $in);  
        }

        // return finished page
        unset($orig);
        return $in;
    }
    
    // HTTP HEADER (better header)
    public function http_header ($type = "text/html")
    {
        header("Content-type: ".$type."; charset=UTF-8"); 
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    // REDIRECT (simple httpd header redirect) 
    public function redirect ($url = "")
    {
        // clear page output buffer
        $this->clear_buffer();
        
        // if no URL default to website root
        if (!$url and $GLOBALS['config']->base_url)
        {
            $url = $GLOBALS['config']->base_url;
        }
        
        // redirect
        if ($url)
        {
            header("Location: ".$url);
            return;
        }
        
        // if still no URL then trigger error page
        trigger_error('Redirect URL not found!', E_USER_ERROR);    
    }

    // VERIFY_REFERER (checks to see if the referrer is set to the website url)
    public static function verify_referer ()
    {
        if (strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']))
            return true;
        else
            return false;
    }

    // CLEAR BUFFER
    public function clear_buffer ()
    {
        $status = ob_get_status();
        for ($c = 0; $c < ($status['level'] + 1); $c++)
        {
            ob_end_clean();
        }
    }

// end html class
}

?>
