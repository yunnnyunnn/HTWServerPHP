<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class My_Controller extends CI_Controller {
	
	var $user_id = NULL;
	var $user_email = NULL;

	///經驗值
	var $insert_share=1;
	var $insert_share_with_photo=2;
	var $share_liked=1;
	var $answer_question=1;
	var $answer_question_with_photo=2;
	var $answer_is_liked=1;
	var $answer_is_best_answer=10;
    
    // 費用
    var $current_payment_per_question = 1;
	
	public function __construct()
	{    
		parent::__construct();
        $this->load->model('user_model');
        $this->load->model('device_model');
        $this->load->model('user_medal_model');

		if(isset($_GET['howeatoken']))
		{
			$this->load->model('howeatoken_model');
            
            $howeatoken = $this->input->get('howeatoken', TRUE);
            
			$where = array(
				'howeatoken' => md5($howeatoken)
				
			);
			$result = $this->howeatoken_model->get_howeatoken($where);
			if($result->num_rows()>0)
			{
				$this->user_id = $result->row()->user_id;
				$this->user_email = $result->row()->user_email;
			}
			else
			{
				$echo = array('status' => 'fail' , 'msg' => 'Error validating access token');
				echo json_encode($echo);
                exit();
			}
		}
		else
		{
			$is_login = $this->session->all_userdata();
			if(!$is_login||empty($is_login['token']))
			{
				redirect('/signin');
			}
			else
			{
				$this->user_id = $is_login['user_id'];
				$this->user_email = $is_login['user_email'];
			}
		}
	}

	public function update_user_exp($user_id,$exp)
	{
		$field=array('user_exp');
		$where=array('user_id' => $user_id );
		$user_data=$this->user_model->get_user($field,$where);
        $new_exp = $user_data->row()->user_exp + $exp;
		$updatefield=array('user_exp' => $new_exp );
		$result=$this->user_model->update_user($where,$updatefield);
        return $new_exp;
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
    
    public function get_device_token($receiver_array)
    {
        $device_token_array = array();
        foreach ($receiver_array as $receiver) {
            
            $where = array(
                           
                           'device.user_id' => $receiver,
                           
                           );
            
            $query_device = $this->device_model->get_device($where);
            $query_device_result = $query_device->result();
            
            if($query_device->num_rows() > 0)
            {
                foreach ($query_device_result as $single_device) {
                    if (!in_array($single_device->device_token, $device_token_array)&&$single_device->device_token)
                    {
                        $data = array (
                                       'device_token' => $single_device->device_token,
                                       'device_type' => $single_device->device_type,
                                       );
                        $device_token_array[] = $data;
                    }
                    
                }
            }
            
        }
        return $device_token_array;
    }
    
    public function user_pay_money($payer_id, $payment) {
        
        $field = array('user_money');
        $where = array('user_id'=>$payer_id);
        $user_data=$this->user_model->get_user($field,$where);
        
        if($user_data->num_rows() == 0) {
            return FALSE;
        }
        
        $user_money = $user_data->row()->user_money;
        
        $user_money = $user_money - $payment;
        
        if ($user_money<0) {
            return FALSE;
        }
        
        
		$updatefield=array('user_money' => $user_money );
		
        return $this->user_model->update_user($where,$updatefield);

    }
    
    public function user_money_enough_checker($payer_id, $payment) {
        
        $field = array('user_money');
        $where = array('user_id'=>$payer_id);
        $user_data=$this->user_model->get_user($field,$where);
        
        if($user_data->num_rows() == 0) {
            return FALSE;
        }
        
        $user_money = $user_data->row()->user_money;
        
        $user_money = $user_money - $payment;
        
        if ($user_money<0) {
            return FALSE;
        }
        else {
            return TRUE;
        }
        
    }
    
    
}