<?
class News_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
	function insert_news($news_data_array)
	{
		$this->db->insert('news',$news_data_array);
		return $this->db->insert_id();
	}
	
	function get_news($news_id)
	{
		$this->db->order_by('news_time','desc');
		return $this->db->where($news_id)->get('news');
	}
	
	function delete_news($delete_field)
	{
		if($this->db->where($delete_field)->delete('news'))
		return TRUE;
		return FALSE;
	}
	
	function update_news($news_array,$update_field)
	{
		if($this->db->where($update_field)->update('news',$news_array))
		return TRUE;
		return FALSE;
	}
	
	function get_news_and_comment($get_field)
	{
		$this->db->join('news_comment','news.news_id=news_comment.news_id');
		$this->db->where($get_field);
		return $this->db->get('news');
	}
}
?>