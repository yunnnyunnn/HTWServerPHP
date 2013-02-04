<?

class User_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
	function get_user($where)
	{		
		return $this->db->where($where)->get('user');
	}
	
	function insert_user($data)
	{
		$this->db->insert('user',$data);
		return $this->db->insert_id();
	}
	
	function update_user($where,$data)
	{
		return $this->db->where($where)->update('user',$data);	
	}
	
	function delete_user($where)
	{
		return $this->db->where($where)->update('user');	
	}
}