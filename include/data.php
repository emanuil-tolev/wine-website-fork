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
    // defines
    public $stop_page;
    public $languages;

    // constuctor
    public function __construct ()
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
                                               'change' => 'Cambiar Idioma'
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
                                              ),
                                 'tr' => array(
                                               'name'   => 'Türkçe',
                                               'change' => 'Lisan Değiştir'
                                              ),
				 'zh_CN' => array(
					       'name'	=> '简体中文',
					       'change'	=> '选择语言'
					      )
                                );
    }
}
?>
