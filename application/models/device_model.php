<?php

class Device_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
	function get_device($where, $field = '*')
	{
        $this->db->join('user','device.user_id = user.user_id');
        
        $this->db->select($field);

		return $this->db->where($where)->get('device');
	}
	
	function insert_device($data)
	{
        if ($this->db->insert('device',$data)) {
            return $this->db->insert_id();
        }
        else {
            return 0;
        }
	}
	
	function update_device($where,$data)
	{
		return $this->db->where($where)->update('device',$data);	
	}
	
	function delete_device($where)
	{
		return $this->db->where($where)->delete('device');
	}
}