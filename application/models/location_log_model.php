<?

class Location_log_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
	function get_location_log($where,$row = '')
	{		
		$this->db->order_by('location_log_time','DESC');
		if(isset($row)&&is_numeric($row))
		{
			$this->db->limit($row);
		}
		
		return $this->db->where($where)->get('location_log');
	}
	
	function get_group_by_location_log($time,$group_by = '')
	{
		//$this->db->order_by('location_log_id','DESC');
		
		//if(isset($distinct)&&!empty($distinct))
		//{
		//	$this->db->group_by($distinct);
		//}
	
		$query=$this->db->query('SELECT * FROM (SELECT * FROM location_log WHERE location_log_time > '."'".$time."'".' order by location_log_time desc) AS t group by '.$group_by);
		//return $this->db->where($where)->get('location_log');
		return $query;
	}
	
	
}

	