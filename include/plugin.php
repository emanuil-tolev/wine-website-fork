<?php
/*
  simple plugin loader    
  by Jeremy Newman <jnewman@codeweavers.com>
*/  

/*
    called from html object during template construction.
    to use in template:
        add: <!--EXEC:[module?foo=bar;baz=taz]-->
    params are loaded after the question mark. The plugin can
    then used the $this->params array.
*/

class plugin
{
    var $module;
    var $root;
    var $params = array();
    
    // constructor
    function plugin ($module, $root)
    {
        $this->module = $module;
        $this->root = $root;
    }
    
    // exec the plugin and get the contents
    function get ()
    {
        // split params from path
        $this->get_params();
            
        // build path
        $path = $this->root.'/include/plugins/'.$this->module.'.php';
    
        // load module if file exists
        if (file_exists($path))
        {
            // output buffer to this object
            ob_start();
            include($path);
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
        // not found, return error message
        return '<span style="font-size: 24pt; color: red;">500</span> <b>'.$this->module.'</b> plugin not found!';
    }
    
    // split the params off the module name (private)
    function get_params ()
    {
        if (ereg("\?", $this->module))
        {
            $arr = split("\?", $this->module, 2);
            $this->module = $arr[0];
            $vars = split(";", $arr[1]);
            foreach ($vars as $var)
            {
                list($a, $b) = split("\=", $var, 2);
                $this->params[$a] = $b;
            }
        }
    }
    
}

?>
