<?php

/*
  WineHQ
  by Jeremy Newman <jnewman@codeweavers.com>
*/

$file_root = ".";
require($file_root."/include/"."incl.php");

// choose the mode based on the $view var
switch (true)
{
  // view page
  case ($page):
    $text .= view_page($page);
    break;
  
  // view wwn issue
  case ($wwn):
    $text .= wwn_view_issue($wwn);
    break;

  // view latest annouce
  case ($announce):
    $text .= view_announce($announce);
    break;

  // default mode (Home Page)
  default:
    $show_nav = 1;
    $text .= home_page();
	break;
}

// display the page
$html->page = $html->frame_table(
                                 $html->frame_tr($html->frame_td($text)),
                                 "99%", 0, ""
                                );
$html->showpage($config->theme, $config->site_name);

//done
exit();

// load and display a template page
function view_page ($view)
{
    global $config, $html;
    $template = $html->template("base", $view);
    return $html->theme_box($config->theme, "box_title", $html->template_title, "100%", $template, '10', 'white', 'topMenu');
}

// load the home page
function home_page ()
{
    global $file_root, $config, $html;
	
	// get aboout box
	$about_box = $html->theme_box($config->theme, "box_title", "About Wine", "99%", $html->template("base", 'home_about'), '10', 'white', 'topMenu');
	
	// get link to latest release
	$latest_box = $html->theme_box($config->theme, "box_title", "Latest Release", "97%", $html->template("base", 'wine_release'), '10', 'white', 'topMenu');
	
	// get wwn news
	$wwn = wwn_get_list($config->wwn_xml_path);
	$wwn_box = $html->theme_box($config->theme, "box_title", "Wine Weekly News", "97%", wwn_issues_list($wwn[0], $wwn[1], 3), '10', 'white', 'topMenu');
	
    // sponsor box
    $sponsor_box = $html->template("base", 'sponsor');
    
	// load the template for home page and fill in
	$vars = array(
	              'about_box'  => $about_box,
			      'latest_box' => $latest_box,
			      'wwn_box' => $wwn_box,
                  'sponsor_box' => $sponsor_box
			     );
	$text = $html->template($config->theme, 'home_page', $vars);	 
	
	return $text;
}

// load the annouce file for latest version of wine
function view_announce ($announce)
{
    global $file_root, $config, $html;
    
    $announce = "http://cvs.winehq.com/cvsweb/~checkout~/wine/ANNOUNCE?rev=".$announce;
    if ($announce = @join("",file($announce)))
    {
        // display page
        return $html->theme_box($config->theme, "box_title", 'Announce', "100%", $html->format_msg($announce), '10', 'white', 'topMenu');
    }
    $html->redirect($file_root);
}

// end of file
?>
