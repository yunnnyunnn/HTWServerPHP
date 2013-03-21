<?php

class Share_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
    
    
    function get_share($where, $limit = 0, $offset = 0)
    {
        $this->db->order_by('share_time','DESC');
        //$this->db->select('user_id, share_id, share_content, share_weather_type, share_photo_url, share_latitude, share_longitude, timediff(share_time, now()) as share_time, (select count(share_likes_id) from share_likes where share_id = share.share_id) as share_likes, (select user_nickname from user where user_id = share.user_id) as user_nickname');
        $this->db->select('user_id, share_id, share_content, share_weather_type, share_photo_url, share_latitude, share_longitude, timediff(share_time, now()) as share_time, (select user_nickname from user where user_id = share.user_id) as user_nickname');
        if ($limit !=0)
        {
            return $this->db->where($where)->get('share', $limit, $offset);

        }
        else
        {
            return $this->db->where($where)->get('share');
   
        }
        
    }
    
    
    function insert_share($data)
    {
        if($this->db->insert('share',$data))
            return TRUE;
		else
            return FALSE;
    }
    
    function delete_share($where)
    {
        if($this->db->where($where)->delete('share'))
            return TRUE;
		else
            return FALSE;
    }
    
    function update_share($data)
    {
        if($this->db->update('share', $date))
            return TRUE;
        else
            return FALSE;
    }
    
}