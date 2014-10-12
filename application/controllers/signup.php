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
            $this->load->model('user_medal_model');
			$this->load->model('share_model');
			$this->load->model('device_model');
			$this->load->model('howeatoken_model');
            $this->load->library('facebook_verification');

        }
	}
	public function index()
	{
		$this->load->view('signup_view');
	}
	
    /*
    public function change_user_password()
    {
        $msg = '';
		$status = '';
		$echo_data = array();
        
        $user_email = $this->input->post('user_email',TRUE);
		$new_user_password = $this->input->post('new_user_password',TRUE);
        
        if (!isset($_POST["user_email"])||!isset($_POST["new_user_password"])) {
            $status = 'fail';
            $msg = 'missing post value';
        }else {
            
            $where = array(
            
            'user_email'=>$user_email
            
            );
            $data = array(
            
            'user_password'=>md5($new_user_password)
            
            );
            
            $result = $this->user_model->update_user($where, $data);
            if ($result){
                $status = 'ok';
                $msg = 'change password successfully';

            }
            else {
                $status = 'fail';
                $msg = 'something went wrong when updating database';

            }
            
            
        }
        
        $echo_data['status'] = $status;
		$echo_data['msg'] = $msg;
		echo json_encode($echo_data);
    }
    */
    public function transfer_posts_from_old_server_to_new()
    {
        $msg = '';
		$status = '';
		$echo_data = array();
        
		$old_user_id = $this->input->post('old_user_id',TRUE);
		$new_user_id = $this->input->post('new_user_id',TRUE);
        
        if (!isset($_POST["old_user_id"])||!isset($_POST["new_user_id"])) {
            $status = 'fail';
            $msg = 'missing post value';
        }
        else {
            $response = $this->curl->simple_post('http://yunnnyunnn.com/transfer.php', array('userID'=>$old_user_id), array(CURLOPT_BUFFERSIZE => 10));
            $responseArray = json_decode($response, true);
            
            $shares = $responseArray['shares'];
            
            foreach($shares as $share){
                
                $x = $share['x'];
                $y = $share['y'];
                $weather = $share['weather'];
                $pic = $share['pic'];
                if($pic != "") {
                    $pic = "http://yunnnyunnn.com/weatherData$pic";
                }
                $msg = $share['msg'];
                $time = $share['time'];
                
                
                $timeObj = strtotime($time);
                
                $new_time = date('Y-m-d H:i:s', strtotime('+14 hours', $timeObj));
                
                $data = array(
                              'user_id'=>$new_user_id,
                              'share_content'=>$msg,
                              'share_weather_type'=>$weather,
                              'share_photo_url'=>$pic,
                              'share_latitude'=>$x,
                              'share_longitude'=>$y,
                              'share_time'=>$new_time,
                              'share_likes'=>0
                              );
                
                $result = $this->share_model->insert_share($data);
                
            }
            
            $status = 'ok';
            $msg = 'transfer_posts_from_old_server_to_new succeed';
            $echo_data['data'] = $shares;

        }
        
        
        
        
        $echo_data['status'] = $status;
		$echo_data['msg'] = $msg;
		echo json_encode($echo_data);
    }
    
    public function login_with_other_service() {
        
        $msg = '';
        $status = '';
        $echo_data = array();
        
        // 1. confirm service type
        $service_type = $this->input->post('service_type',TRUE);
        if ($service_type == 0) { // 0 = facebook
            
            // 2. verify with this service
            $fb_token = $this->input->post('fb_token',TRUE);
            $fb_id = $this->input->post('fb_id',TRUE);

            $result_email = $this->facebook_verification->verify_token_with_facebook($fb_token, $fb_id);
            
            if ($result_email) {
                
                // 3. check if email valid
                $user_email = $this->input->post('user_email',TRUE);
                if(filter_var($user_email, FILTER_VALIDATE_EMAIL))
                {
                    
                    // 4. check if email used
                    $field = array('*');
                    $query = $this->user_model->get_user($field ,array('user_email'=>$user_email));
                    if($query->num_rows()>0) // login without password
                    {
                        
                        $device_type = $this->input->post('device_type',TRUE);
                        if(empty($device_type)||!is_numeric($device_type))
                        {
                            $msg = 'wrong device';
                            $status = 'fail';
                        }
                        else
                        {
                            
                            if($device_type == '1' || $device_type == '2' || $device_type == '3')
                            {
                                $user_id = $query->row()->user_id;
                                $status = 'ok';
                                $msg = 'sign in successfully';
                                $echo_data['login_type'] = 0; // 0 as login
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
                                ///
                                $device_data = array();
                                $device_data['device_token'] = '';
                                $device_data['user_id'] = $user_id;
                                $device_data['device_type'] = $device_type;
                                
                                $device_id = $this->device_model->insert_device($device_data);
                                if($device_id>0)
                                {
                                    $echo_data['device_id'] = $device_id;
                                }
                                else
                                {
                                    $msg = 'Sign in fail : Database Error';
                                    $status = 'fail';
                                }
                            }
                            else {
                                $msg = 'other device';
                                $status = 'fail';
                            }

                            
                        }

                        
                    }
                    else { // sign up without password
                        
                        $device_type = $this->input->post('device_type',TRUE);
                        if(empty($device_type)||!is_numeric($device_type))
                        {
                            $msg = 'wrong device';
                            $status = 'fail';
                        }
                        else {
                            
                            $user_nickname = $this->input->post('user_nickname',TRUE);
                            
                            $device_data = array();
                            $user_data = array();
                            $validate = FALSE;
                            $user_data['user_email'] = $user_email;
                            $user_data['user_nickname'] = $user_nickname;
                            if($device_type == '1' || $device_type == '2' || $device_type == '3')
                            {
                                
                                $user_password = $fb_id; // use fb id first because token might be too long
                                $user_data['user_password'] = md5($user_password);
                                $device_token = $this->input->post('device_token',TRUE);
                                $device_data['device_token'] = $device_token;
                
                                
                                $user_id = time();

                                
                                $where_data = array('user_id' => $user_id);
                                $query = $this->user_model->get_user($field ,$where_data);
                                $count = $query->num_rows();
                                if($count>0)
                                    $user_id = $user_id.$count;
                                
                                $user_data['user_id'] = $user_id;
                                $user_data['user_medal'] = 0;
                                $user_data['user_money'] = 3;
                                
                                if($this->user_model->insert_user($user_data))
                                {
                                    
                                    $user_exp = 0;

                                    $this->check_and_insert_user_medal($user_id, $user_exp);
                                    
                                    
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
                                        $echo_data['login_type'] = 1; // 1 as sign up
                                        $msg = 'Sign Up OK';
                                        $status = 'ok';
                                        //							$session = array(
                                        //								'user_id'=>$user_id ,
                                        //								'user_email' => $user_email ,
                                        //								'token' => md5(uniqid(rand(), TRUE))
                                        //							);
                                        //							$this->session->set_userdata($session);
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
                            else
                            {
                                $msg = 'other device';
                                $status = 'fail';
                            }
                            
                        }
                        
                        
                        
                        
                    }
                    
                    
                    
                }
                else {
                    $msg = 'E-mail is not valid';
                    $status = 'fail';
                }
                
                
            }
            else {
                $status = 'fail';
                $msg = 'verification failed with facebook';
            }
            
        }
        else {
            $status = 'ok';
            $msg = 'other service';
        }
        
        $echo_data['status'] = $status;
        $echo_data['msg'] = $msg;
        echo json_encode($echo_data);

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
                    $user_id = 0;
                    $shares = array();
                    $user_exp = 0;
                    if (isset($_POST["user_id"])) {
                        $user_id = $this->input->post('user_id',TRUE);
                        
                        $response = $this->curl->simple_post('http://yunnnyunnn.com/transfer.php', array('userID'=>$user_id), array(CURLOPT_BUFFERSIZE => 10));
                        $responseArray = json_decode($response, true);
                        
                        $user_data['user_exp'] = $responseArray['money'];
                        $user_exp = $responseArray['money'];
                        $shares = $responseArray['shares'];
                                                
                    }
                    else {
                       $user_id = time();
                    }
					
					$where_data = array('user_id' => $user_id);
					$query = $this->user_model->get_user($field ,$where_data);
					$count = $query->num_rows();
					if($count>0)
					$user_id = $user_id.$count;
					
					$user_data['user_id'] = $user_id;
                    $user_data['user_medal'] = 0;
                    $user_data['user_money'] = 3;

					if($this->user_model->insert_user($user_data))
					{
                        
                        $this->check_and_insert_user_medal($user_id, $user_exp);

                        
                        if (isset($_POST["user_id"])) {
                            
                            foreach($shares as $share){
                                
                                
                                $x = $share['x'];
                                $y = $share['y'];
                                $weather = $share['weather'];
                                $pic = $share['pic'];
                                if($pic != "") {
                                    $pic = "http://yunnnyunnn.com/weatherData$pic";
                                }
                                $msg = $share['msg'];
                                $time = $share['time'];
                                
                                
                                $timeObj = strtotime($time);

                                $new_time = date('Y-m-d H:i:s', strtotime('+14 hours', $timeObj));

                                $data = array(
                                              'user_id'=>$user_id,
                                              'share_content'=>$msg,
                                              'share_weather_type'=>$weather,
                                              'share_photo_url'=>$pic,
                                              'share_latitude'=>$x,
                                              'share_longitude'=>$y,
                                              'share_time'=>$new_time,
                                              'share_likes'=>0
                                              );
                                
                                $result = $this->share_model->insert_share($data);
                                
                            }
                            
                            
                            
                        }
                        
                        
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
//							$session = array(
//								'user_id'=>$user_id ,
//								'user_email' => $user_email ,
//								'token' => md5(uniqid(rand(), TRUE))
//							);
//							$this->session->set_userdata($session);
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
    
    
    public function check_and_insert_user_medal($user_id, $new_exp)
    {
        $medal_array = unserialize(MEDAL_WITH_EXP);
        
        foreach ($medal_array as $medal_number => $exp) {
            if ($new_exp>=$exp) {
                
                $field = array('*');
                $medal_data = array(
                                    'user_id' => $user_id,
                                    'medal_id' => $medal_number
                                    );
                $medal_checker = $this->user_medal_model->get_user_medal($field, $medal_data);
                if ($medal_checker->num_rows()==0) { // 必須要他沒有這個medal才能增加一個medal
                    
                    if($this->user_medal_model->insert_user_medal($medal_data)) {
                        // ok
                    }
                    else {
                        // fail when insert medal
                    }
                }
                
            }
        }
    }
	
}