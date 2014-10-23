<?php

/*
    WineHQ Website
    Announce plugin (grabs latest announce from git)
    by Jeremy Newman <jnewman@codeweavers.com>
       Vince C. <v_cadet@yahoo.fr> (back-reference in 'changes' section)
*/

// ver from URL
$ver = PAGE_PARAMS;

// build tag and announce URL
$tag = ($ver == "latest") ? "master" : "wine-" . urlencode($ver);
$announce = $config->git_tree . "/wine.git/" . $tag . ":ANNOUNCE";

// announce title
$title = "";

// load announce
if ($arr = file($announce))
{
    $in_header = 1;
    $x = 0;
    while (list($c,$val) = each($arr))
    {
        if ($c == 0)
            $title = $html->encode(trim($arr[$c]));

        $arr[$c] = $html->urlify($arr[$c]);

        if (preg_match("/^--------------------/", $arr[$c])) $in_header = 0;
        else if (preg_match("/^Bugs fixed/", $arr[$c])) $in_bugs = 1;
        else if (preg_match("/^Changes since/", $arr[$c]))
        {
            // Link to previous versions in changes
            $in_bugs = 0;
            $arr[$c] = preg_replace( '/[[:digit:].]+(-rc[[:digit:]]+)?/', '<a title="Changes since \\0" href="./\\0">\\0</a>', $arr[$c] );
        }

        if ($in_header)
        {
            $arr[$c] = preg_replace("/AUTHORS/",
                                    "<a href=\"".$config->git_tree."/wine.git/".$tag.":\\0\">\\0</a>",
                                    $arr[$c]);
        }           
        else if ($in_bugs)
        {
            $arr[$c] = preg_replace("/^( +)([0-9]+)/",
                                    "\\1<a href=\"".$config->bug_system."\\2\">\\2</a>",
                                    $arr[$c]);
        }

        $x++;
    }
    $announce = join("",$arr);

    // display page
    $html->meta_og['title'] = $title;
    $html->page_title .= " - {$title}";
    echo $html->pre($announce);
}
else
{
    // not found, redirect to home page
    $html->redirect($html->_web_root);
}

// done
?>
