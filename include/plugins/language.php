<?php

/*
    WineHQ Website
    Select Language Plugin
    by Jeremy Newman <jnewman@codeweavers.com>
*/

global $html, $config, $data;

switch ($this->params['cmd'])
{
    // list languages
    case "list":
        foreach ($config->languages as $lang)
        {
            $row = array(
                         'lang'     => $lang,
                         'langFull' => $data->languages[$lang]['name'],
                         'langHelp' => $data->languages[$lang]['change']
                        );
            echo $html->template('local', 'global/lang_row', $row, 1);
        }
        break;

    // set the language based on the URL
    default:
        // if specified, switch to lang
        if (defined('PAGE_PARAMS') and in_array(PAGE_PARAMS, $config->languages))
        {
            setcookie("lang", PAGE_PARAMS, time()+60*60*24*365, "{$config->base_root}/");
            $html->redirect($config->base_url);
            exit();
        }
}

?>
