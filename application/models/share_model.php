<?php

class Share_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
    
    
    function get_share($where, $field = '*', $limit = 0, $offset = 0)
    {
        $this->db->order_by('share_time','DESC');
        $this->db->join('user','share.user_id = user.user_id');
        
        $this->db->select($field);
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