<?php

class Push_queue_ios_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
        
    function insert_push_queue_iOS($data)
    {
        if($this->db->insert('push_queue_iOS',$data))
            return TRUE;
		else
            return FALSE;
    }
    
    
}