<?

class Share_model extends CI_Model
{
	function _construct()
	{
		parent::_construct();
	}
	
    
    
    function get_share($where)
    {
        $this->db->order_by('share_time','ASC');
		return $this->db->where($where)->get('share');
    }
    
    
    function insert_share($data)
    {
        if($this->db->insert('share',$data))
            return TRUE;
		else
            return FALSE;
    }
    
    function delete_share($where)
    {
        if($this->db->where($where)->delete('share'))
            return TRUE;
		else
            return FALSE;
    }
    
    function update_share($data)
    {
        if($this->db->update('share', $date))
            return TRUE;
        else
            return FALSE;
    }
    
}