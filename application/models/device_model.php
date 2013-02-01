<?

class Device_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
	function get_device($where)
	{		
		return $this->db->where($where)->get('device');
	}
	
	function insert_device($data)
	{
		return $this->db->insert('device',$data);
	}
	
	function update_device($where,$data)
	{
		return $this->db->where($where)->update('device',$data);	
	}
	
	function delete_device($where)
	{
		return $this->db->where($where)->update('device');	
	}
}