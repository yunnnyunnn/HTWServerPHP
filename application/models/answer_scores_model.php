<?php

class Answer_scores_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	function get_answer_scores($field = '*' ,$where,$limit=25,$offset=0)
	{		
        $this->db->order_by('answer_scores_id','DESC');
        $this->db->join('user','answer_scores.user_id = user.user_id');
		$this->db->select($field);
        
		return $this->db->where($where)->get('answer_scores',$limit,$offset);
	}
	
    function get_answer_scores_count($where)
    {
		return $this->db->where($where)->count_all_results('answer_scores');
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