<?php

/*
  WineHQ Register Form
  by Jeremy Newman <jnewman@codeweavers.com>
*/

global $config, $html;

// form var
$form =& $this->params['form'];

// dbfile
$db_file = '/tmp/'.$this->params['db'].'.txt';

// main
if (isset($_GET['done']))
{
    // show done template
    echo $html->template("base", $form."_done");
}
else if ($_POST['submit'])
{
    // check vars
    if (!$_POST['q']['name'] or !$_POST['q']['email'] or !preg_match("/\@/", $_POST['q']['email']))
    {
        $vars = array(
                      'name' => $_POST['q']['name'],
                      'email' => $_POST['q']['email'],
                      'comment' => $_POST['q']['comment'],
                      'error' => $html->span("Form is not complete!", 'class="error"')
                     );
        echo $html->template("base", $form."_form", $vars);
    }
    else
    {
        // append form data to file
        if (file_exists($db_file))
        {
            $fh = fopen($db_file, 'a');
            fwrite($fh, preg_replace("/[\t\r\n]+/", " ", stripslashes($_POST['q']['name'])));
            fwrite($fh, "\t");
            fwrite($fh, preg_replace("/[\t\r\n]+/", " ", stripslashes($_POST['q']['email'])));
            fwrite($fh, "\t");
            fwrite($fh, preg_replace("/[\t\r\n]+/", " ", stripslashes($_POST['q']['comment'])));
            fwrite($fh, "\n");
            fclose($fh);
            
            // redirect to done page
            $html->redirect($_SERVER['PHP_SELF'].'?done=1');
            exit();
        }
        else
        {
            echo $html->span("ERROR: DB File $db_file NOT Found!", 'class="error"');
        }
    }
}
else 
{
    // display form
    echo $html->template("base", $form."_form");
}

// done
?>
