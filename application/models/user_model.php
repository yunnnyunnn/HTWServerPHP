<?

class User_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
	function get_user($field , $where)
	{		
		$this->db->select($field);
		return $this->db->where($where)->get('user');
	}
	
	function insert_user($data)
	{
		return $this->db->insert('user',$data);
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