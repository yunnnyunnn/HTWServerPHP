<?php

class Location_log_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
    
    function insert_location_log($data)
    {
        return $this->db->insert('location_log',$data);
    }
	    
	function get_location_log($where, $field = '*',$row = '')
	{		
		$this->db->order_by('location_log_time','DESC');
        $this->db->select($field);
		if(isset($row)&&is_numeric($row))
		{
			$this->db->limit($row);
		}
		
		return $this->db->where($where)->get('location_log');
	}
	
	function get_group_by_location_log($time,$group_by = '')
	{
		if(isset($time)&&!empty($time))
		{
			$query=$this->db->query('SELECT * FROM (SELECT * FROM location_log WHERE location_log_time > '."'".$time."'".' order by location_log_time desc) AS t group by '.$group_by);
		}
		else{
			$query=$this->db->query('SELECT * FROM (SELECT * FROM location_log order by location_log_time desc) AS t group by '.$group_by);
		}
		
		return $query;
	}
	
}

	