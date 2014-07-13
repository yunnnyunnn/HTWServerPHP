<?php
class Notification_model extends My_Model
{
	function __construct()
	{
		parent::__construct();
	}
	
	function insert_notification($data)
	{
		$result = $this->db->insert('notification',$data); 
		if($result == TRUE)
		{
			return $this->db->insert_id();
		}else
		{
			return false;
		}
	}
	
	function get_notification($where,$limit=0,$offset=0)
	{
		$this->db->order_by('notification_time','DESC');
        $this->db->join('user','notification.user_id_sender = user.user_id');
        $this->db->select('notification.*, timediff(notification_time, now()) as notification_timediff, user.user_nickname as sender_user_nickname');
        if ($limit !=0)
        {
            return $this->db->where($where)->get('notification', $limit, $offset);

        }
        else
        {
            return $this->db->where($where)->get('notification');
   
        }
		
	}
    
    function get_notification_with_answer($where_in)
    {
        $this->db->join('answer', 'answer.question_id = notification.post_id AND answer.user_id = notification.user_id_receiver', 'left');
        $this->db->select('COUNT( DISTINCT post_id ) AS total_notification, COUNT( DISTINCT question_id ) AS total_answer');
        $this->db->where('notification.notification_type', 5);
        $this->db->where_in('notification.user_id_receiver', $where_in);
        return $this->db->get('notification');
    }
	
	function get_notifitcation_count($where)
	{
		return $this->db->where($where)->count_all_results('notification'); 
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