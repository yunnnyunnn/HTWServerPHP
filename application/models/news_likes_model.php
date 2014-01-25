<?php
class News_likes_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
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

        function get_likes($get_field)
        {
                $this->db->join('user','user.user_id=news_likes.user_id');
                $this->db->select(array('news_likes.user_id','user_nickname'));
		return $this->db->where($get_field)->get('news_likes');
        }
	
	
}

 ?>