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
	
	public function __construct()
	{    
		parent::__construct();
        $this->load->model('user_model');

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
    
    
    
}