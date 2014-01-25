<?php

class User_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	function get_user($field , $where)
	{		
		$this->db->select($field);
		return $this->db->where($where)->get('user');
	}
	
	function get_user_with_device($field ,$where)
	{		
		
		$this->db->join('device','user.user_id = device.user_id','left');
		return $this->db->where($where)->get('user');
	}
	
	function insert_user($data)
	{
		return $this->db->insert('user',$data);
	}
	
	function update_user($where,$data)
	{
		return $this->db->where($where)->update('user',$data);	
	}
	
	function delete_user($where)
	{
		return $this->db->where($where)->update('user');	
	}
}