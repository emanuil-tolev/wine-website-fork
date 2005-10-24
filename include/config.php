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
    function config ($path = "")
    {
        // exit if config not found
        if (!file_exists($path))
        {
            echo 'config file not found!';
            exit();
        }
        
        // read global config file
        $this->readConfig($path);
        
        // navigation
	$this->nav = array(
	    'About' => array(
                'About'           => '{$root}/site/about',
                'Introduction'    => '{$root}/site/about',
                'Features'        => '{$root}/site/wine_features',
                'Screenshots'     => '{$root}/site?ss=1',
                'Contributing'    => '{$root}/site/contributing',
                'News'            => '{$root}/site?news=archive',
                'Press'           => '{$root}/site/press',
                'License'         => '{$root}/site/license'
            ),
           'Support' => array(
                'Support'         => '{$root}/site/support',
                'Download'        => '{$root}/site/download',
                'Documentation'   => '{$root}/site/documentation',
                'FAQ'             => '{$root}/site/docs/wine-faq/index',
                'Wine Wiki'       => 'http://wiki.winehq.org',
                'HowTo'           => '{$root}/site/howto',
                'Bug Tracking'    => 'http://bugs.winehq.org/',
                'Applications Database'    => 'http://appdb.winehq.org',
                'Mailing Lists'   => '{$root}/site/forums'
           ),
           'Development' => array(
                'Development'     => '{$root}/site/development',
                'CVS'             => '{$root}/site/cvs',
                'Sending Patches' => '{$root}/site/sending_patches',
                'To Do Lists'     => 'http://wiki.winehq.org/TodoList',
                'Fun Projects'    => '{$root}/site/fun_projects',
                'Janitorial'      => 'http://wiki.winehq.org/JanitorialProjects',
                'Winelib'         => '{$root}/site/winelib',
                'Status'          => '{$root}/site/status',
                'Resources'       => '{$root}/site/resources'
           )
       );

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
