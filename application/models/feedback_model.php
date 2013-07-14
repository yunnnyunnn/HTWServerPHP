<?php

class Feedback_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
    
    
    function get_feedback($where, $field = '*', $limit = 0, $offset = 0)
    {
        $this->db->order_by('feedback_time','DESC');
        $this->db->join('user','feedback.user_id = user.user_id');
        
        $this->db->select($field);
        if ($limit !=0)
        {
            return $this->db->where($where)->get('feedback', $limit, $offset);

        }
        else
        {
            return $this->db->where($where)->get('feedback');
   
        }
        
    }
        
    function insert_feedback($data)
    {
        if($this->db->insert('feedback',$data))
            return TRUE;
		else
            return FALSE;
    }
    
    
}