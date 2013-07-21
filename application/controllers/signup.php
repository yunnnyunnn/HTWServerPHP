<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Signup extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();	
		$is_login = $this->session->all_userdata();
		if(isset($is_login['token']))
		{
			redirect('/');
		}
		else
		{
			$this->load->model('user_model');
			$this->load->model('share_model');
			$this->load->model('device_model');
			$this->load->model('howeatoken_model');
		}
	}
	public function index()
	{
		$this->load->view('signup_view');
	}
	
	public function signup_service()
	{
		$msg = '';
		$status = '';
		$echo_data = array();

		$user_email = $this->input->post('user_email',TRUE);
		$user_nickname = $this->input->post('user_nickname',TRUE);
		//$user_email = 'qq12345886@hotmail.com';
		$device_type = $this->input->post('device_type',TRUE);
		//$device_type = '1';
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
			$user_data['user_email'] = $user_email;
			$user_data['user_nickname'] = $user_nickname;
			if($device_type == '1' || $device_type == '2' || $device_type == '3')
			{
                
                $user_password = $this->input->post('user_password',TRUE);
				if(!empty($user_password))
				{
					$user_data['user_password'] = md5($user_password);
                    $device_token = $this->input->post('device_token',TRUE);
                    $device_data['device_token'] = $device_token;
                    
                    $validate = TRUE;
				}
				else{
					$msg = 'Sign Up fail : Please Enter Password';
					$validate = FALSE;
				}
                
				
			}
			else if($device_type == '4')
			{
				$user_password = $this->input->post('user_password',TRUE);
				$user_password_again = $this->input->post('user_password_again',TRUE);
				if(!empty($user_password)&&!empty($user_password_again))
				{	
					if($user_password == $user_password_again)
					{
						$validate = TRUE;
						$user_data['user_password'] = md5($user_password);
					}
					else{
						$msg = 'Sign Up fail : Password Is Not the same';
						$validate = FALSE;
					}
				}
				else{
					$msg = 'Sign Up fail : Please Enter Password';
					$validate = FALSE;
				}
			}
			else
			{
				$validate = FALSE;
				$msg = 'wrong device';
			}
			
			if($validate)
			{	
				$field = array('user_id');
				$query = $this->user_model->get_user($field ,array('user_email'=>$user_email));
				if($query->num_rows()>0)
				{
					$msg = 'Sign Up fail : Already Sign Up';
					$status = 'fail';
				}
				else
				{
					$user_id = time();
					$where_data = array('user_id' => $user_id);
					$query = $this->user_model->get_user($field ,$where_data);
					$count = $query->num_rows();
					if($count>0)
					$user_id = $user_id.$count;
					
					$user_data['user_id'] = $user_id;
					if($this->user_model->insert_user($user_data))
					{
						$device_data['user_id'] = $user_id;
						$device_data['device_type'] = $device_type;
						$echo_data['user_id'] = $user_id;
						///howeatoken 
						$howeatoken = NULL;
						$num = 57 ;
						for ($i=1;$i<=$num;$i=$i+1)
						{
							$c=rand(1,3);
							if($c==1){$a=rand(97,122);$b=chr($a);}
							if($c==2){$a=rand(65,90);$b=chr($a);}	
							if($c==3){$b=rand(0,9);}						
							$howeatoken=$howeatoken.$b;
						}	
						$howeatoken_data = array(
							'howeatoken' => md5($howeatoken),
							'user_id' => $user_id
						);
						if($this->howeatoken_model->insert_howeatoken($howeatoken_data))
						{
							$echo_data['howeatoken'] = $howeatoken;
						}
						///
                        $device_id = $this->device_model->insert_device($device_data);
						if($device_id>0)
						{
                            $echo_data['device_id'] = $device_id;
							$msg = 'Sign Up OK';
							$status = 'ok';
							$session = array(
								'user_id'=>$user_id ,
								'user_email' => $user_email ,
								'token' => md5(uniqid(rand(), TRUE))
							);
							$this->session->set_userdata($session);
						}
						else
						{
							$msg = 'Sign Up fail : Database Error';
							$status = 'fail';
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
	
}