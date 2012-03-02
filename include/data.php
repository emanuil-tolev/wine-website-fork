<?php

/*
  WineHQ
  misc data class
  by Jeremy Newman <jnewman@codeweavers.com>
*/  

/*
 * data class - misc global hard data
 */

class data
{
    // create shopping cart object
    function data ()
    {        
        // plugin stop pages
        // add the template path here to make anything after the path in the URL
        // be stored in the static PAGE_PARAMS
        $this->stop_page = array(
                                 'announce',
                                 'interview',
                                 'lang',
                                 'news',
                                 'wwn'
                                );

        // available languages
        $this->languages = array(
                                 'en' => array(
                                               'name'   => 'English',
                                               'change' => 'Change Language'
                                              ),
                                 'de' => array(
                                               'name'   => 'Deutsch',
                                               'change' => 'Sprache ändern'
                                              ),
                                 'es' => array(
                                               'name'   => 'Español',
                                               'change' => 'Cambiar la Lengua'
                                              ),
                                 'fr' => array(
                                               'name'   => 'Français',
                                               'change' => 'Changez la langue'
                                              ),
                                 'he' => array(
                                               'name'   => 'עברית',
                                               'change' => 'החלפת השפה'
                                              ),
                                 'pl' => array(
                                               'name'   => 'Polski',
                                               'change' => 'Zmień język'
                                              ),
                                 'pt' => array(
                                               'name'   => 'Português',
                                               'change' => 'Mudar a língua'
                                              )
                                 'pt' => array(
                                               'name'   => 'Türkçe',
                                               'change' => 'Lisan Değiştir'
                                              )
                                );

        return;
    }
}
?>
