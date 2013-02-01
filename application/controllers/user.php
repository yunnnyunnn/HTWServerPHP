<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();	
		$this->load->model('user_model');
		$this->load->model('device_model');
	}
	public function index()
	{
		echo 'user login';
	}
	public function sign_in()
	{
		$user_email = $this->input->post('user_email',TRUE);
		$password = $this->input->post('password',TRUE);
		$sess = array(
			'user_name'=>'ding' , 
			'phone' => '0983781731' ,
			'token' => $token = md5(uniqid(rand(), TRUE))
		);
		$this->session->set_userdata('user',$sess);
	}
	
	public function sign_up()
	{
		$msg = '';
		$status = '';
		$user_email = $this->input->post('user_email',TRUE);
		$device_type = $this->input->post('device_type',TRUE);
		if(!filter_var($user_email, FILTER_VALIDATE_EMAIL))
		{
			$msg = 'E-mail is not valid';
			$status = 'fail';
		}
		else
		{
			$device_data = array();
			$user_data = array();
			$validate = FALSE;
			if($device_type == '1' || $device_type == '2' || $device_type == '3')
			{
				$device_token = $this->input->post('device_token',TRUE);
				$device_data['device_token'] = $device_token;
				$user_data['user_email'] = $user_email;
				$validate = TRUE;
			}
			else if($device_type == '4')
			{
				$user_password = $this->input->post('user_password',TRUE);
				$user_data['user_email'] = $user_email;
				$user_data['user_password'] = md5($user_password);
				$validate = TRUE;
			}
			else
			{
				$validate = FALSE;
			}
			if($validate)
			{	
				$query = $this->user_model->get_user($user_data);
				if($query->num_rows()>0)
				{
					$msg = 'Sign Up fail : Already Sign Up';
					$status = 'fail';
				}
				else
				{
					$user_id = $this->user_model->insert_user($user_data);
					$device_data['user_id'] = $user_id;
					$device_data['device_type'] = $device_type;
					if($this->device_model->insert_device($device_data))
					{
						$msg = 'Sign Up OK';
						$status = 'success';
					}
					else
					{
						$msg = 'Sign Up fail : Database Error';
						$status = 'fail';
					}
				}
			}
			else
			{
				$msg = 'Sign Up fail : Wrong device';
				$status = 'fail';
			}	
		}
		echo json_encode(array('status' => $status , 'msg' => $msg));
	}
	private function test()
	{
		
		
		
	}
	
}