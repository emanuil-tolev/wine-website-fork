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
                echo 'config file not found!';
                exit();
            }
        
            // read global config file
            $this->readConfig($path);
        }
        
        // navigation
	$this->nav = array(
	    'WineHQ Menu' => array(
                'WineHQ'          => '{$root}',
                'AppDB'           => 'http://appdb.winehq.org',
                'Bugzilla'        => 'http://bugs.winehq.org/',
                'Wine Wiki'       => 'http://wiki.winehq.org',
            ),
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
           'Download' => array(
                'Get Wine Now'        => '{$root}/site/download'
            ),
           'Support' => array(
                'Support'         => '{$root}/site/support',
                'Getting Help'    => '{$root}/site/getting_help',
                'FAQ'             => 'http://wiki.winehq.org/FAQ',
                'Documentation'   => '{$root}/site/documentation',
                'HowTo'           => '{$root}/site/howto',
                'Live Support Chat' => '{$root}/site/irc',
                'Paid Support'    => 'http://www.codeweavers.com/'
           ),
           'Development' => array(
                'Development'     => '{$root}/site/development',
                'Developers Guide' => '{$root}/site/docs/winedev-guide/index',
                'Mailing Lists'   => '{$root}/site/forums',
                'GIT'             => '{$root}/site/git',
                'Sending Patches' => '{$root}/site/sending_patches',
                'To Do Lists'     => 'http://wiki.winehq.org/TodoList',
                'Fun Projects'    => '{$root}/site/fun_projects',
                'Janitorial'      => 'http://wiki.winehq.org/JanitorialProjects',
                'Winelib'         => '{$root}/site/winelib',
                'Status'          => '{$root}/site/status',
                'Resources'       => '{$root}/site/resources',
                'WineConf'        => '{$root}/site/wineconf'
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
