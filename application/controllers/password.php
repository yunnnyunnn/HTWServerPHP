<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Password extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();	
        $this->load->model('password_reset_request_model');
        $this->load->model('user_model');
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
    
    public function reset_password()
    {
        $status = '';
		$msg = '';
        $user_id = $this->input->post('user_id',TRUE); 
        $howeatoken = $this->input->get('howeatoken', TRUE);
        $password_encrypt = $this->input->post('password',TRUE);
        $password_again_encrypt = $this->input->post('password_again',TRUE);
        if($password_encrypt&&$password_again_encrypt)
        {
            $params = array('key' => md5($howeatoken,TRUE));
            $this->load->library('DES', $params);
            $password = $this->des->decrypt($password_encrypt);
            $password_again = $this->des->decrypt($password_again_encrypt);
            if($password == $password_again)
            {
                $reset = $this->user_model->update_user(array('user_id'=>$user_id),array('user_password'=>md5($password)));
                if($reset)
                {
                      $status='ok';
                      $msg = 'Password Reset Successfully';
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
            $msg = 'Password Reset Error : Please enter password';
        }
        echo json_encode(array('msg'=>$msg,'status'=>$status));
    }





}