<?php

class Answer_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	function get_answer_without_user($field='*',$where)
	{	
		$this->db->order_by('answer_time','ASC');	
		$this->db->select($field);
		return $this->db->where($where)->get('answer');
	}
	
	function get_answer($field='*',$where)
	{	
		$this->db->order_by('answer_time','ASC');	
		$this->db->join('user','answer.user_id = user.user_id');
		$this->db->select($field);
		return $this->db->where($where)->get('answer');
	}
    
    function get_total_answer_rate()
    {
        return $this->db->query('SELECT ((SELECT COUNT( DISTINCT question_id )FROM answer WHERE question_id IN (SELECT DISTINCT question_id FROM question LEFT JOIN notification ON question.question_id = notification.post_id WHERE notification.notification_type =5 ) ) / (SELECT COUNT( DISTINCT question_id ) AS question_count FROM question LEFT JOIN notification ON question.question_id = notification.post_id WHERE notification.notification_type =5 )) as answer_rate');
    }
	
    function get_answer_count($where)
    {
        return $this->db->where($where)->count_all_results('answer');
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
		return $this->db->where($where)->delete('answer');	
	}
}