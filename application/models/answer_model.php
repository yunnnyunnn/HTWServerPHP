<?php

class Answer_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
	function get_answer($field='*',$where)
	{	
		$this->db->order_by('answer_time','ASC');	
		$this->db->join('user','answer.user_id = user.user_id');
		$this->db->select($field);
		return $this->db->where($where)->get('answer');
	}
	
	function insert_answer($data)
	{
		$this->db->insert('answer',$data);
		return $this->db->insert_id();
	}
	
	function update_answer($where,$data)
	{
		return $this->db->where($where)->update('answer',$data);	
	}
	
	function delete_answer($where)
	{
		return $this->db->where($where)->update('answer');	
	}
}