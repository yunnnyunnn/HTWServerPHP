<?php

class Push_queue_android_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
        
    function insert_push_queue_android($data)
    {
        if($this->db->insert('push_queue_android',$data))
            return TRUE;
		else
            return FALSE;
    }
    
    
}