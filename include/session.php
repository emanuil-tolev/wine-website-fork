<?

/*
  WineHQ
  by Jeremy Newman <jnewman@codeweavers.com>
*/  

/*
 * session.php - session handler functions
 * sessions are stored in a mysql table
 */

class session
{
    // create session object
    function session ($name)
    {
        // open session file (not needed for DB access)        
        function _session_open ($save_path, $session_name) { return true; }
        // close session file (not needed for DB access)
        function _session_close () { return true; }
        // read session
        function _session_read ($key)
        {
            global $db;
            $result = $db->query("SELECT data FROM session_list WHERE session_id = '".$key."'");
            if (!$result) { return null; }
            $r = $db->fetch($result);
            return $r->data; 
        }
        // write session to DB
        function _session_write ($key, $value)
        {
            global $db, $REMOTE_ADDR;
            $db->query("REPLACE session_list VALUES ('$key', '".addslashes($value)."', NOW())");
            return true;
        }
        // delete current session
        function _session_destroy ($key)
        {
            global $db;
            $db->query("DELETE FROM session_list WHERE session_id = '$key'");
            return true;
        }
        // clear old sessions (auto-expire)
        function _session_gc ($maxlifetime)
        {
            global $db;
            $db->query("DELETE FROM session_list WHERE to_days(now()) - to_days(stamp) >= 7");
            $db->query("DELETE FROM session_msg WHERE to_days(now()) - to_days(stamp) > 1");    
            return true;
        }
        // set name for this session
        $this->name = $name;
        session_name($this->name);        
        // setup session object
        session_set_save_handler("_session_open", 
                                 "_session_close", 
                                 "_session_read",
                                 "_session_write", 
                                 "_session_destroy", 
                                 "_session_gc");
        // default timeout on session cookie
        session_set_cookie_params(time() + 3600 * 360);
        // start the loaded session
        session_start();        
    }

    // register variables into session
    function register ($var)
    {
        session_name($this->name);
        session_register($var);
    }

    // destroy session
    function destroy ()
    {
        session_name($this->name);
        session_destroy();
    }
    
    // msgs will be displayed on the Next page view of the same user
    function addmsg ($text, $color = "black", $redir = '')
    {
        global $db;
        $text = '<font color="'.$color.'">'.$text.'</font>';
        $db->query("INSERT INTO session_msg VALUES ('".session_id()."', '".addslashes($text)."', NOW())");
    }
    
    // output msg_buffer and clear it.
    function dumpmsgbuffer ()
    {
        global $config, $db, $html;
        $result = $db->query("SELECT message FROM session_msg WHERE session_id = '".session_id()."'");
        // return if no messages
        if ($db->num_rows($result) == 0) { return; }
        while ($r = $db->fetch($result))
        {
            $msg .= $html->p(stripslashes($r->message), "align=center");
        }
        // erase the messages
        $db->query("DELETE FROM session_msg WHERE session_id = '".session_id()."'");        
        // display the messaage
        $text = $html->br().$html->frame_start('',"250");
        $text .= $html->frame_tr(
                                 $html->frame_td($msg),
                                 "white"
                                );
        $text .= $html->frame_end('');
        return $text;
    }
}
// end session
?>
