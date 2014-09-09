<?php

class Password_reset_request_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
    function insert_password_reset_request($data)
    {
        return $this->db->insert('password_reset_request',$data);
    }
    
    function get_password_reset_request($where,$field = '*')
    {
        $this->db->select($field);
        return $this->db->where($where)->get('password_reset_request');
    }
    
    function delete_password_reset_request($where)
    {
        return $this->db->where($where)->delete('password_reset_request');	
    }
    
}