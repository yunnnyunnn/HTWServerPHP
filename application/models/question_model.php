<?

class Question_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
	function get_question($where)
	{		
		$this->db->order_by('question_time','ASC');
		return $this->db->where($where)->get('question');
	}
	
	function insert_question($data)
	{
		return $this->db->insert('question',$data);
	}
	
	function update_question($where,$data)
	{
		return $this->db->where($where)->update('question',$data);	
	}
	
	function delete_question($where)
	{
		return $this->db->where($where)->update('question');	
	}
}