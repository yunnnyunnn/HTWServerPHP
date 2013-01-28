<?
class News_comment_model extends CI_Controller
{
	function _construct()
	{
		parent::_construct();
	}
	
	function get_comment($get_id)
	{
		$this->db->order_by('news_comment_time','desc');
		return $this->db->where($get_id)->get('news_comment');
	}
	
	function insert_comment($comment_array)
	{
		$this->db->insert('news_comment',$comment_array);
		return $this->db->insert_id();
	}
	
	function delete_comment($delete_field)
	{
		if($this->db->where($delete_field)->delete('news_comment'))
		return TRUE;
		return FALSE;
	}
	
	function update_news_comment($update_array,$update_field)
	{
		if($this->db->where($update_field)->update('news_comment',$update_array))
		return TRUE;
		return FALSE;
	}
}

 ?>