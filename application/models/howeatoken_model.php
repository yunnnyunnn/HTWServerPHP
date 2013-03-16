<?php

class Howeatoken_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
	function get_howeatoken($field , $where)
	{		
		$this->db->select($field);
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