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
        // PHP ERROR CODES
        $this->err = array(
                           1    => 'E_ERROR',
                           2    => 'E_WARNING',
                           4    => 'E_PARSE',
                           8    => 'E_NOTICE',
                           16   => 'E_CORE_ERROR',
                           32   => 'E_CORE_WARNING',
                           64   => 'E_COMPILE_ERROR',
                           128  => 'E_COMPILE_WARNING',
                           256  => 'E_USER_ERROR',
                           512  => 'E_USER_WARNING',
                           1024 => 'E_USER_NOTICE',
                           2047 => 'E_ALL',
                           2048 => 'E_STRICT'
                          );
        
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

        return;
    }
}
?>