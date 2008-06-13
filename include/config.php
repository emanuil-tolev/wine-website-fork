<?php

/*
  WineHQ
  by Jeremy Newman <jnewman@codeweavers.com>
*/  

/*
 * global params class
 */
 
class config 
{ 
    function config ()
    {
        // get files passed
        $files = func_get_args();
    
        // loop and parse files
        foreach ($files as $path)
        {
            // exit if config not found
            if (!file_exists($path))
            {
                echo "config file {$path} not found!";
                exit();
            }
        
            // read global config file
            $this->readConfig($path);
        }
    // end of config()
    }
    
	// reads config from text file
	function readConfig ($file)
	{	
		$fd = fopen ($file, "r",TRUE);
		while (!feof ($fd)) {
	        $buffer = trim(fgets($fd, 4096));
			if (ereg('^\#', $buffer)) continue;
			if ($buffer == "") continue;
			$arr = preg_split('/:\s+/',$buffer,2);
            $arr[1] = preg_replace("/<br>/","\n",$arr[1]);
            if (ereg('^\@', $arr[0]))
            {
                // array
                $arr[0] = ereg_replace('\@', '', $arr[0]); 
                $this->$arr[0] = preg_split('/,\s+/', $arr[1]);
            }
            else if (ereg('^\%', $arr[0]))
            {
                // assoc array
                $arr[0] = ereg_replace('\%', '', $arr[0]);
                $this->$arr[0] = array();
                $params = preg_split('/,\s+/', $arr[1]);
                while (list($n, $m) = each($params))
                {
                    list($key, $val) = preg_split('/\|/', $m, 2);
                    $this->$arr[0] = array_merge($this->$arr[0], array($key => $val));
                }
            }
            else
            {
                // string
                $this->$arr[0] = $arr[1];
            }
		}
		fclose ($fd);
	// end of readConfig
	}
    
}
?>
