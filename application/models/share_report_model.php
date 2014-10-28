<?php

class Share_report_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
    
    
    function get_share_report($where, $field = '*', $limit = 0, $offset = 0)
    {
        $this->db->order_by('share_report_time','DESC');
        $this->db->join('share','share_report.share_id = share.share_id');
        
        $this->db->select($field);
        if ($limit !=0)
        {
            return $this->db->where($where)->get('share_report', $limit, $offset);

        }
        else
        {
            return $this->db->where($where)->get('share_report');
   
        }
        
    }
        
    function insert_share_report($data)
    {
        if($this->db->insert('share_report',$data))
            return TRUE;
		else
            return FALSE;
    }
    
    
}