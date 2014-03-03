<?php

class Share_comment_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
    
    
    function get_share_comment($where, $field = '*',$limit=25,$offset=0)
    {
        $this->db->order_by('share_comment_time','DESC');
        $this->db->join('user','share_comment.user_id = user.user_id');
        $this->db->select($field);
		return $this->db->where($where)->get('share_comment',$limit,$offset);
    }
    
    function get_share_comment_count($where)
    {
		return $this->db->where($where)->count_all_results('share_comment');
    }
    
    function insert_share_comment($data)
    {
        if($this->db->insert('share_comment',$data))
            return TRUE;
		else
            return FALSE;
    }
    
    function delete_share_comment($where)
    {
        if($this->db->where($where)->delete('share_comment'))
            return TRUE;
		else
            return FALSE;
    }
    
    function update_share_comment($data)
    {
        if($this->db->update('share_comment', $date))
            return TRUE;
        else
            return FALSE;
    }
    
}