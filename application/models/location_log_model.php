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
	
}

	