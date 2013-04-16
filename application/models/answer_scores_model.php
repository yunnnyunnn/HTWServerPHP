<?php

class Answer_scores_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
	function get_answer_scores($field = '*' ,$where)
	{		
		$this->db->select($field);
		return $this->db->where($where)->get('answer_scores');
	}
	
	function insert_answer_scores($data)
	{
		return $this->db->insert('answer_scores',$data);
	}
	
	function update_answer_scores($where,$data)
	{
		return $this->db->where($where)->update('answer_scores',$data);	
	}
	
	function delete_answer_scores($where)
	{
		return $this->db->where($where)->delete('answer_scores');	
	}
}