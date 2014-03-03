<?php

class Share_likes_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
    function get_share_likes($where,$field = '*',$limit=25,$offset=0)
    {
        //$this->db->select('share_likes_id, share_id, user_id, (select user_nickname from user where user_id = share_likes.user_id) as user_nickname');
        $this->db->order_by('share_likes_id','DESC');
        $this->db->join('user','share_likes.user_id = user.user_id');
        $this->db->select($field);
		return $this->db->where($where)->get('share_likes',$limit,$offset);
    }
    
    function get_share_likes_count($where)
    {
		return $this->db->where($where)->count_all_results('share_likes');
    }
    
    function insert_share_likes($data)
    {
        if($this->db->insert('share_likes',$data))
            return TRUE;
		else
            return FALSE;
    }
    
    function delete_share_likes($where)
    {
        if($this->db->where($where)->delete('share_likes'))
            return TRUE;
		else
            return FALSE;
    }
    
    function update_share_likes($data)
    {
        if($this->db->update('share_likes', $date))
            return TRUE;
        else
            return FALSE;
    }
    
}