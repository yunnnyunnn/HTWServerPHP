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
		if($this->db->insert('question',$data))
		return TRUE;
		else
		return FALSE;
	}
}