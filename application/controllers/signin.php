<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Signin extends CI_Controller {
	
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
			$this->load->model('share_model');
			$this->load->model('device_model');
		}
	}
	public function index()
	{
		$this->load->view('signin_view');
	}
	
	public function signin_service()
	{
		$status = '';
		$msg = '';
		$echo_data = array();
		$user_email = $this->input->post('user_email',TRUE);
		$user_password = $this->input->post('user_password',TRUE);//123456
		$device_type = $this->input->post('device_type',TRUE);
		$mapping_code = $this->input->post('mapping_code',TRUE);

		if(empty($device_type)||!is_numeric($device_type))
		{
			$msg = 'wrong device';
			$status = 'fail';
		}
		else
		{
			if(!filter_var($user_email, FILTER_VALIDATE_EMAIL))
			{
				$msg = 'E-mail is not valid';
				$status = 'fail';
			}
			else
			{
				$auth = FALSE;
				$where = array();
				if($device_type == '1' || $device_type == '2' || $device_type == '3')
				{
					if(!empty($mapping_code))
					{
						for($i=0;$i<60;$i++)
						{
							$mapping_code_server = sha1((time()-$i).'Tim.William.Brad');  
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
						$msg = 'Missing mapping code';
						$status = 'fail';		
					}
				}
				else if($device_type == '4')
				{
					if(!empty($user_password))
					{
						$where['user_email'] = $user_email;
						$where['user_password'] = md5($user_password);
						$auth = TRUE;
					}
					else
					{
						$msg = 'Missing Password';
						$status = 'fail';
					}	
				}
				else
				{
					$msg = 'wrong device code';
					$status = 'fail';
				}
				
				if($auth)
				{
					$field = array('*');
					$query = $this->user_model->get_user($field , $where);
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
		}
		$echo_data['status'] = $status;
		$echo_data['msg'] = $msg;	
		echo json_encode($echo_data);
	}
}