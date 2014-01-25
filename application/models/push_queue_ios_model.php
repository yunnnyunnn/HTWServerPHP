<?php

class Push_queue_ios_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
        
    function insert_push_queue_iOS($data)
    {
        if($this->db->insert('push_queue_iOS',$data))
            return TRUE;
		else
            return FALSE;
    }
    
    
}