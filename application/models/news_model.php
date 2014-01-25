<?php
class News_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	function insert_news($news_data_array)
	{
		return $this->db->insert('news',$news_data_array); 
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
		$this->db->where($get_field);
                $this->db->select('*','news_id as new_news_id')->from('news');
		//$this->db->join('news_comment','news.news_id=news_comment.news_id','left');
                $this->db->order_by('news_time','desc');

		
		return $this->db->get();
	}
}
?>