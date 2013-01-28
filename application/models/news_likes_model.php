<?
class News_likes_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
	function insert_like($news_like_array)
	{
		$this->db->insert('news_likes',$news_like_array);
		return $this->db->insert_id();
	}
	
	function delete_like($delete_field)
	{
		if($this->db->where($delete_field)->delete('news_likes'))
		return true;
		return false;
	}
	
	
}

 ?>