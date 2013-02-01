<?php
class session 
{
	function session(){
        $this->_set_session();
    }

    function _set_session()
	{
		if(isset($_GET['session_id']))
		{
			session_id($_GET['session_id']);
		}
        session_start();
    }
	
	function get_session_id()
	{
		return session_id();
	}
	
    function set_userdata($session_name,$session_value)
	{
        $_SESSION[$session_name] = $session_value;
    }
    function userdata($session_name)
	{
        if(isset($_SESSION[$session_name]))
            return $_SESSION[$session_name];
        return false;
    }
    function unset_userdata($session_name)
	{
        if(isset($_SESSION[$session_name]))
            unset($_SESSION[$session_name]);
    }
}
?>