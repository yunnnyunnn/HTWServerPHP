<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Signin extends CI_Controller {
	
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
		$this->load->view('signin_view');
	}
	
	public function signin_service()
	{
		$status = '';
		$msg = '';
		$echo_data = array();
		$user_email = trim($this->input->post('user_email',TRUE));
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
				$where['user_email'] = $user_email;
				if($device_type == '1' || $device_type == '2' || $device_type == '3')
				{
                    
                    
                    $field = array('user.user_id,device_type');
					$device_where['device_type'] = $device_type;
					$device_where['user_email'] = $user_email;
					$query = $this->user_model->get_user_with_device($field , $device_where);
					if($query->num_rows()>0)
					{
						if(!empty($user_password))
						{
							$where['user_password'] = md5($user_password);
							$field = array('*');
							$query = $this->user_model->get_user($field , $where);
							if($query->num_rows()>0)
							{
                                
                                
								$user_id = $query->row()->user_id;
								$status = 'ok';
								$msg = 'sign in successfully';
								$echo_data['user_id'] = $user_id;
                                $echo_data['user_nickname'] = $query->row()->user_nickname;
                                
                                
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
							}
							else
							{
								$msg = 'Email or Password Error';
								$status = 'fail';
							}
						}
						else
						{
							$msg = 'Missing Password';
							$status = 'fail';
						}	
					}
					else
					{
						$msg = 'undefined device';
						$status = 'fail';
					}
                    
                    
                    /*
					if(!empty($mapping_code))
					{
						$mapping_code_server = sha1('HOWEATHER_Tim.William.Brad.Allan.Henry');  
						if($mapping_code_server == $mapping_code)
						{
							$field = array('*');
							$query = $this->user_model->get_user($field , $where);
							if($query->num_rows()>0)
							{
								//send email
								
							}
							else
							{
								$msg = 'no user email';
								$status = 'fail';	
							}			
						}
					}
					else
					{
						$msg = 'Missing mapping code';
						$status = 'fail';		
					}
                     */
				}
				else if($device_type == '4')
				{
					$field = array('user.user_id,device_type');
					$device_where['device_type'] = $device_type;
					$device_where['user_email'] = $user_email;
					$query = $this->user_model->get_user_with_device($field , $device_where);
					if($query->num_rows()>0)
					{
						if(!empty($user_password))
						{
							$where['user_password'] = md5($user_password);
							$field = array('*');
							$query = $this->user_model->get_user($field , $where);
							if($query->num_rows()>0)
							{
								$user_id = $query->row()->user_id;
								$status = 'ok';
								$msg = 'sign in successfully';
								$session = array(
									'user_id'=> $user_id ,
									'user_email' => $query->row()->user_email ,
									'token' => $token = md5(uniqid(rand(), TRUE))
								);
								$this->session->set_userdata($session);
								$echo_data['user_id'] = $user_id;
							}
							else
							{
								$msg = 'Email or Password Error';
								$status = 'fail';
							}
						}
						else
						{
							$msg = 'Missing Password';
							$status = 'fail';
						}	
					}
					else
					{
						$msg = 'undefined Password';
						$status = 'fail';
					}
				}
				else
				{
					$msg = 'wrong device code';
					$status = 'fail';
				}	
			}
		}
		$echo_data['status'] = $status;
		$echo_data['msg'] = $msg;	
		echo json_encode($echo_data);
	}
	
	private function check_sign_vcode()
	{
		
		
		
	}
}