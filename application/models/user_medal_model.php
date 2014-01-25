<?php

class User_medal_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	function get_user_medal($field , $where)
	{		
		$this->db->select($field);
		return $this->db->where($where)->get('user_medal');
	}
	
	
	
	function insert_user_medal($data)
	{
		return $this->db->insert('user_medal',$data);
	}
	
	function update_user_medal($where,$data)
	{
		return $this->db->where($where)->update('user_medal',$data);	
	}
	
	function delete_user_medal($where)
	{
		return $this->db->where($where)->update('user_medal');	
	}
}