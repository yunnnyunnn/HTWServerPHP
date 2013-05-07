<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends My_Controller {
	
	public function __construct()
	{
		parent::__construct();	
		$this->load->model('user_model');
		$this->load->model('share_model');
        $this->load->model('location_log_model');
	}
	public function index()
	{
		//
		
		//$this->get_one_user();
	}
	
	public function get_one_user()
	{
		$status = '';
		$msg = '';
		$echo_data = array();
		$user_id = $this->input->post('user_id',TRUE);
		if(empty($user_id)||!is_numeric($user_id))
		{
			$status = 'fail';
			$msg = 'missing user id';
		}
		else
		{
			$field = array('*');
			$where_data = array('user_id'=>$user_id);
			$user_data = $this->user_model->get_user($field,$where_data);
			if($user_data->num_rows()>0)
			{
				$field = array('user_id');
				$where_data = array('user_exp >'=>$user_data->row()->user_exp);
				$user_rank = $this->user_model->get_user($field,$where_data);
				
				$where_data = array('user_id'=>$user_id);
				$user_share = $this->share_model->get_share($where_data);			
		
				$status = 'ok';
				$msg = 'ok';
				$echo_data['user_share_count'] = $user_share->num_rows();
				$echo_data['user_rank'] = $user_rank->num_rows()+1;
				$echo_data['userdata'] = $user_data->result();
			}
			else
			{
				$status = 'fail';
				$msg = 'no such user';
			}			
		}
		$echo_data['status'] = $status;
		$echo_data['msg'] = $msg;
		echo json_encode($echo_data);	
	}
    
    public function insert_location_log()
    {
        
        $user_id = $this->user_id;
        
        $location_latitude = $this->input->post('location_latitude', TRUE);
        $location_longitude = $this->input->post('location_longitude', TRUE);
        
        // 防止沒有傳post value
        if(!isset($_POST["location_latitude"]) OR !isset($_POST["location_longitude"]))
        {
            echo json_encode(array('msg' => 'insert location post value not set',
                                   'status' => 'fail'));
            return;
        }
        
        
        $data = array(
                      'user_id'=>$user_id,
                      'location_latitude'=>$location_latitude,
                      'location_longitude'=>$location_longitude,
                      'location_log_time'=>date("Y-m-d H:i:s"),
                      );
        
        $result = $this->location_log_model->insert_location_log($data);
        
        echo json_encode(array('msg' => 'insert share ok',
                               'status' => 'ok'));
    }
	
	
}