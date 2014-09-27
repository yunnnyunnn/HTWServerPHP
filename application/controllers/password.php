<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Password extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();	
        $this->load->model('password_reset_request_model');
        $this->load->model('user_model');
        $this->load->library('mailer');
    }

   //check user password reset request
    public function check_request()
    {
        $post_data = json_decode(file_get_contents("php://input"));
        $status = '';
		$msg = '';
        $user_email = '';
        
        //$user_id = $this->input->post('user_id',TRUE); 
        //$prr_token = $this->input->post('prr_token',TRUE);
        $user_id = $post_data->user_id;
        $prr_token = $post_data->prr_token;
      
        
        if(empty($prr_token) || is_numeric($user_id)==false)
        {
            $status = 'fail';
            $msg = 'post value error';
            echo json_encode(array('msg' => $msg,
                                   'status' => $status));
            return;
        }
        $where = array('user_id' => $user_id, 'token' => $prr_token , 'end_datetime' => NULL);
        $query = $this->password_reset_request_model->get_password_reset_request($where);
        
        if($query->num_rows()>0)
        {
            $field = array('user_email');
			$where_data = array('user.user_id'=>$user_id);
			$user_data = $this->user_model->get_user($field,$where_data);
            
            if($user_data->num_rows()>0)
			{
                $user_email = $user_data->row()->user_email;
                $status = 'ok';
                $msg = 'pendding password reset request';
            }
            else
            {
                $status = 'fail';
                $msg = 'user is not exist';
            }
        }
        else
        {
            $status = 'fail';
            $msg = 'no password reset request';
        }
        
        echo json_encode(array('msg' => $msg, 'status' => $status , 'user_email' =>$user_email ));
    }
    
    public function reset_request()
    {
        $status = '';
		$msg = '';
        $user_email = $this->input->post('user_email',TRUE);
        if(filter_var($user_email, FILTER_VALIDATE_EMAIL) == false)
        {
            $status = 'fail';
            $msg = 'invalid email';
            echo json_encode(array('msg' => $msg,
                                   'status' => $status));
            return;
        }
        
        $field = array('user_id');
        $where_data = array('user.user_email'=>$user_email);
		$user_data = $this->user_model->get_user($field,$where_data);
        if($user_data->num_rows()>0)
		{
            $user_id = $user_data->row()->user_id;
            
            $delete_where = array('user_id' => $user_id , 'end_datetime' => NULL);
            $this->password_reset_request_model->delete_password_reset_request($delete_where);
            $dt_now = date('Y-m-d H:i:s');
            $prr_token = md5(strtotime($dt_now).$user_id.uniqid("",true));
            
            $prr_data = array('user_id' => $user_id , 'token' => $prr_token , 'requested_datetime' => $dt_now);
            $result = $this->password_reset_request_model->insert_password_reset_request( $prr_data);
            if($result)
            {
                $to = $user_email;
                $subject = 'Howeather Password';
                $body = '<p><b>Forgot your password ?</b>Reset it below.</p>
                <p><a href="http://howeather.com/password/#/reset/'.$user_id.'/'.$prr_token.'">Reset password</a></p>';
                $result = $this->mailer->send_mail($to,$subject,$body);
                if($result)
                {
                    $status = 'ok';
                    $msg = 'new password reset request finished';
                }
                else
                {
                    $status = 'fail';
                    $msg = 'send reset mail fail.';
                }
            }
            else
            {
                $status = 'fail';
                $msg = 'new password reset request fail';
            }
        }
        else
        {
            $status = 'fail';
            $msg = 'user is not exist';
        }
        
        echo json_encode(array('msg' => $msg, 'status' => $status ));
    }
    
     
    public function reset_password()
    {
        $status = '';
		$msg = '';
        $user_id = $this->input->post('user_id',TRUE); 
        $password_encrypt = $this->input->post('password',TRUE);
        $password_again_encrypt = $this->input->post('password_again',TRUE);
       //$iv = $this->input->post('iv',TRUE);       
        if($password_encrypt&&$password_again_encrypt)
        {
            $where = array('user_id' => $user_id, 'end_datetime' => NULL);
            $query = $this->password_reset_request_model->get_password_reset_request($where);
            if($query->num_rows()>0)
            {
                $prr_token = $query->row()->token;
                $key = md5($prr_token);
                $iv = substr($prr_token,0,16);
                $params = array('iv' => $iv);
                $this->load->library('DES',$params);
                $password = $this->des->decrypt($password_encrypt,$key);
                $password_again = $this->des->decrypt($password_again_encrypt,$key);
                if($password == $password_again)
                {
                    $result = $this->user_model->update_user(array('user_id'=>$user_id),array('user_password'=>md5($password)));
                    if($result)
                    {
                        $status='ok';
                        $msg = 'Password Reset Successfully';
                        $update_data = array('end_datetime' => date('Y-m-d H:i:s'));
                        $this->password_reset_request_model->update_password_reset_request($where, $update_data);
                    }
                    else
                    {
                        $status='fail';
                        $msg = 'Password Reset Error : Database error';
                    }
                }
                else
                {
                    $status='fail';
                    $msg = 'Password Reset Error : Password is not match';
                }
            }
            else
            {
                $status='fail';
                $msg = 'no pending request';
            }
        }
        else
        {
            $status='fail';
            $msg = 'Password Reset Error : Please enter password';
        }
        echo json_encode(array('msg'=>$msg,'status'=>$status));
    }
    
    





}