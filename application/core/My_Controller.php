<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class My_Controller extends CI_Controller {
	
	var $user_id = NULL;
	var $user_email = NULL;
	
	public function __construct()
	{
        
        
        
		parent::__construct();
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
			$is_login = $this->session->userdata('user');
			if(!$is_login||empty($is_login['token']))
			{
				redirect('/');
			}
			else
			{
				$this->user_id = $is_login['user_id'];
				$this->user_email = $is_login['user_email'];
			}
		}
	}
}