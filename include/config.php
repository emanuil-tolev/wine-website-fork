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
        global $PHP_SELF;
        
		// read global config file
		$config_path = "/etc/winehq.conf";
		$this->readConfig($config_path);
		
        // navigation
		$this->nav = array(
					 'About' => array(
					                  'About' => $this->_file_root.'?page=about',
                                      'Introduction' => $this->_file_root.'?page=about',
                                      'Features' => $this->_file_root.'?page=wine_features',
									  'Screenshots' => $this->_file_root.'?ss=1',
									  'Contributing' => $this->_file_root.'?page=contributing',
									  'News' => $this->_file_root.'?news=archive',
                                      'Press' => $this->_file_root.'?page=press',
                                      'License' => $this->_file_root.'?page=license'
									  ),
					 'Download' => array(
					                     'Download' => $this->_file_root.'?page=download',
                                         'Binaries' => $this->_file_root.'?page=download',
										 'Source' => $this->_file_root.'?page=download_source'
										),
					 'Support' => array(
                                        'Support' => $this->_file_root.'?page=support',
                                        'Documentation' => $this->_file_root.'?page=documentation',
                                        'FAQ' => $this->_file_root.'?page=faq',
										'HowTo' => $this->_file_root.'?page=howto',
										'Bug Tracking' => 'http://bugs.winehq.org/',
                                        'Applications' => $this->_file_root.'?page=supported_applications',
										//'Troubleshooting' => $this->_file_root.'?page=troubleshooting',
										'Forums' => $this->_file_root.'?page=forums'
									   ),
					 'Development' => array(
					                        'Development' => $this->_file_root.'?page=development',
                                            'CVS' => $this->_file_root.'?page=cvs',
                                            //'Developer Hints' => $this->_file_root.'?page=developer_hints',
                                            'Sending Patches' => $this->_file_root.'?page=sending_patches',
                                            'To Do Lists' => $this->_file_root.'?page=todo_lists',
                                            'Fun Projects' => $this->_file_root.'?page=fun_projects',
                                            'Janitorial' => $this->_file_root.'?page=janitorial',
                                            'Winelib' => $this->_file_root.'?page=winelib',
                                            'Status' => $this->_file_root.'?page=status',
                                            'Resources' => $this->_file_root.'?page=resources'
										   ),
                      'User' => array(
                                      'User' => $this->_file_root.'?page=change_theme',
                                      'Change Theme' => $this->_file_root.'?page=change_theme'
                                     )
			         );
		
    // end of config()
    }
    
	// reads config from text file
	function readConfig ($file)
	{	
		$fd = fopen ($file, "r");
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
