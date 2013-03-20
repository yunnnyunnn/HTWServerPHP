<?php

class Share_comment_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
    
    
    function get_share_comment($where)
    {
        $this->db->order_by('share_comment_time','DESC');
        $this->db->select('share_comment_id, share_id, share_comment_content, timediff(share_comment_time, now()) as share_comment_time, user_id, (select user_nickname from user where user_id = share_comment.user_id) as user_nickname');
		return $this->db->where($where)->get('share_comment');
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