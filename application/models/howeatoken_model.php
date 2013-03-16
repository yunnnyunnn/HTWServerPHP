<?php

class Howeatoken_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
	function get_howeatoken($where)
	{		
		$this->db->select('user.user_id,user.user_email,howeatoken_permission,user.user_id as user_id');
		$this->db->join('user' , 'howeatoken.user_id = user.user_id','left');
		return $this->db->where($where)->get('howeatoken'); 
	}
	
	function insert_howeatoken($data)
	{
		return $this->db->insert('howeatoken',$data);
	}
	
	function update_howeatoken($where,$data)
	{
		return $this->db->where($where)->update('howeatoken',$data);	
	}
	
	function delete_howeatoken($where)
	{
		return $this->db->where($where)->update('howeatoken');	
	}
}