<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();	
		$is_login = $this->session->userdata('user');
		if($is_login||!empty($is_login['token']))
		{
			redirect('/');
		}
		else
		{
			$this->load->model('user_model');
			$this->load->model('device_model');
		}
	}
	public function index()
	{
		$this->load->view('user_view');
	}
	
	public function sign_in_service()
	{
		$status = '';
		$msg = '';
		$echo_data = array();
		$user_email = $this->input->post('user_email',TRUE);
		$user_password = $this->input->post('user_password',TRUE);
		$mapping_code = $this->input->post('mapping_code',TRUE);
		if(!filter_var($user_email, FILTER_VALIDATE_EMAIL))
		{
			$msg = 'E-mail is not valid';
			$status = 'fail';
		}
		else
		{
			$auth = FALSE;
			$where = array();
			if(!empty($mapping_code))
			{
				for($i=0;$i<60;$i++)
				{
					$mapping_code_server = md5((time()+$i)+'Tim.William.Brad'); 
					if($mapping_code_server==$mapping_code)
					{
						$where['user_email'] = $user_email;
						$auth = TRUE;
						break;
					}
				}
			}
			else
			{
				if(!empty($user_password))
				{
					$where['user_email'] = $user_email;
					$where['user_password'] = md5($user_password);
					$auth = TRUE;
				}			
			}
			if($auth)
			{
				$query = $this->user_model->get_user($where);
				if($query->num_rows()>0)
				{
					$status = 'success';
					$msg = 'sign in successfully';
					$session = array(
						'user_id'=>$query->row()->user_id ,
						'user_email' => $query->row()->user_email ,
						'token' => $token = md5(uniqid(rand(), TRUE))
					);
					$this->session->set_userdata('user',$session);
					if($device_type!=4)
					{
						$echo_data['session_id'] = session_id();
					}
				}
				else
				{
					$status = 'fail';
					$msg = 'email or password error';
				}
			}
			else
			{
				$status = 'fail';
				$msg = 'no password';
			}
		}
		$echo_data['status'] = $status;
		$echo_data['msg'] = $msg;	
		echo json_encode($echo_data);
	}
	
	public function sign_up_service()
	{
		$msg = '';
		$status = '';
		$echo_data = array();
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
				$user_password_again = $this->input->post('user_password_again',TRUE);
				$user_data['user_email'] = $user_email;
				if(!empty($user_password)&&!empty($user_password_again))
				{	
					if($user_password==$user_password_again)
					{
						$validate = TRUE;
					}
					else{
						$msg = 'Sign Up fail : Password Is Not the same';
						$validate = FALSE;
					}
				}else
				{
					$msg = 'Sign Up fail : Please Enter Password';
					$validate = FALSE;
				}
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
					$user_data['user_password'] = md5($user_password);
					$user_id = $this->user_model->insert_user($user_data);
					$device_data['user_id'] = $user_id;
					$device_data['device_type'] = $device_type;
					if($this->device_model->insert_device($device_data))
					{
						$msg = 'Sign Up OK';
						$status = 'success';
						$session = array(
							'user_id'=>$user_id ,
							'user_email' => $user_email ,
							'token' => $token = md5(uniqid(rand(), TRUE))
						);
						$this->session->set_userdata('user',$session);
						if($device_type!=4)
						{
							$echo_data['session_id'] = session_id();
						}
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
				$status = 'fail';
			}	
		}
		$echo_data['status'] = $status;
		$echo_data['msg'] = $msg;	
		echo json_encode($echo_data);
	}
	
	private function test()
	{
		
		
		
	}
	
}