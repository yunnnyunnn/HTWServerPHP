<?php
class Notification_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
	function insert_notification($data)
	{
		return $this->db->insert('notification',$data); 
	}
	
	function get_notification($where)
	{
		$this->db->order_by('notification_time','DESC');
        $this->db->select('*, timediff(notification_time, now()) as notification_timediff');
		return $this->db->where($where)->get('notification');
	}
	
	function delete_notification($where)
	{
		if($this->db->where($where)->delete('notification'))
		return TRUE;
		return FALSE;
	}
	
	function update_notification($notification_array,$update_field)
	{
		if($this->db->where($update_field)->update('notification',$notification_array))
		return TRUE;
		return FALSE;
	}
	
}
?>