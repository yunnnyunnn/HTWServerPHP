<?php
class News_comment_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	function get_comment($get_id)
	{
		$this->db->order_by('news_comment_time','desc');
                
                $this->db->join('user','user.user_id=news_comment.user_id');
                $this->db->select(array('news_comment.user_id','user_nickname','news_comment.*'));
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