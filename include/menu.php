<?php

/*
 * menu.php -- Navigation creation Class
 * by Jeremy Newman <jnewman@codeweavers.com>
 */

class menu 
{
    var $menu;
    var $pageName;
    var $path = array();
    var $navbar = array();
    var $set = array();
    var $callback = array();
    var $full;
    var $level = 0;
    var $mode = 0;

    // init
    function menu (&$html, $theme, $lang = 'en')
    {
        // set the file root and current page
        $this->html =& $html;
        $this->set = array(
                           'theme' => $theme,
                           'lang'  => $lang
                          );
    }

    // load the menu and return in html form
    function load ($xml = 'sidebar', $page = null, $template = 'menu')
    {
        // set the page
        $this->full = split('\/',$page);

        // set the template
        $this->set['template'] =& $template;
        
        // load the menu xml
        if (file_exists($this->html->_file_root."/templates/".$this->set['lang']."/".$xml.'.xml'))
            $xml_text = join("",file($this->html->_file_root."/templates/".$this->set['lang']."/".$xml.'.xml'));
        else if (file_exists($this->html->_file_root."/templates/en/".$xml.'.xml'))
            $xml_text = join("",file($this->html->_file_root."/templates/en/".$xml.'.xml'));
        else
            trigger_error(E_WARNING, "Unable to load menu!");
        
        // load XML into array
        $xml2a    = new XMLToArray();
        $root     = $xml2a->parse($xml_text);
        $tree     = array_shift($root["_ELEMENTS"]);
        unset($xml_text, $xml2a, $root);

        // parse the current menu tree
        $this->parse($tree);
        unset($tree);

        // done
        return $this->build();
    }
        
    // loop to show menu
    function parse (&$tree, $level = -1)
    {
        // up the current level
        $level++;
            
        // loop through the elements of this tree
        foreach ($tree["_ELEMENTS"] as $nav)
        {
            // in mode
            if ($this->mode and $level == 0 and $nav['name'] != $this->full[0])
                continue;

            // add item to current path
            array_push($this->path, $nav['name']);

            // store the top level page name
            if ($level == 0)
                $this->pageName = $nav['desc'];

            // set link
            $link = $this->html->_web_root.'/'.join('/',$this->path);
            if (isset($nav['link']))
            {
                // set link to param
                $link = $nav['link'];
                
                // do var replacement on the link
                $link = preg_replace('/\{\$root\}/', $GLOBALS['config']->base_url, $link);
            }
            
            // is this item selected
            $n = ($level - count($this->full)) + 1;
            $selPath = $this->full;
            if ($n != 0)
                array_splice($selPath, $n);
            $sel = join('/',$selPath) == join('/',$this->path) ? 1 : 0;
            unset($selPath, $n);

            // add the item to the menu (if not hidden)
            $nav['hidden'] = isset($nav['hidden']) ? 1 : 0;
            $nav['unfold'] = isset($nav['unfold']) ? 1 : 0;
            if ($nav['hidden'] != 1)
            {
                // add item with template
                $template = isset($nav['template']) ? $nav['template'] : 'item';
                $this->add($template, $nav['name'], $nav['desc'], $link, $level, $sel);
                
                // go into this item and get subitems if selected
                if ($sel or $nav['unfold'])
                    $this->parse($nav, $level);
            }
            
            // store selected path for navbar
            if ($sel)
            {
                $v = array(
                           'desc' => $nav['desc'],
                           'link' => $link
                          );
                array_push($this->navbar, $v);
                unset($v);
            }
            
            // remove this item from the path
            array_pop($this->path);
        }
    }
     
    // build navigation bar
    function nav ()
    {
        // get the nav in the correct order
        $this->navbar = array_reverse($this->navbar);
      
        // if we are overriding the navbar
        if (defined("NAVBAR_MSG"))
            return NAVBAR_MSG;

        // start the nav array
        $nav = array();
        $nav[0] = $this->html->ahref('Home', $this->html->_web_root."/");
        
        // loop and add the sub level links
        for ($c = 0; $c < count($this->navbar); $c++)
        {
            if (isset($this->navbar[$c]['desc']))
            {
                $link = $this->html->ahref($this->navbar[$c]['desc'], $this->navbar[$c]['link']);
                array_push($nav, $link);
            }
        }
        
        // join as a string
        return join(" &gt; ", $nav);
    }

    // add a menu link
    function add ($template, $item, $name, $url = null, $level = 0, $selected = 0)
    {
        // bold select items
        if ($selected)
            $name = $this->html->b($name);
        
        // adust the padding of the indent
        $pad = intval($level) * 0.7;
        
        // vars for template
        $vars = array(
                      'pad'  => "$pad",
                      'item' => "$item",
                      'name' => "$name",
                      'url'  => "$url",
                     );
        
        // highlight for topbar nav
        $vars['sel'] = $selected ? '_s' : '';
        
        // fill in template (cache)
        $this->menu .= $this->html->template($this->set['theme'], $this->set['template']."_".$template."_row", $vars, 0, 1);
    }
    
    // complete and return menu box
    function build ()
    {
        $this->set['body'] = $this->menu;
        $menu = $this->html->template($this->set['theme'], $this->set['template'], $this->set, 0, 1);
        return $menu;
    }    
}

?>
