<?php

class Push_queue_android_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
        
    function insert_push_queue_android($data)
    {
        if($this->db->insert('push_queue_android',$data))
            return TRUE;
		else
            return FALSE;
    }
    
    
}