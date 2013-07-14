<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Feedback extends My_Controller {
	
	public function __construct()
	{
		parent::__construct();
        $this->load->model('user_model');
        $this->load->model('feedback_model');

		

	}
	public function index()
	{
		//
		
		//$this->get_one_user();
	}
    
    public function insert_feedback() {
        
        
        $user_id = $this->user_id;
        
        $feedback_content = $this->input->post('feedback_content', TRUE);
        
        // 防止沒有傳post value
        if(!isset($_POST["feedback_content"]))
        {
            echo json_encode(array('msg' => 'insert feedback value not set',
                                   'status' => 'fail'));
            return;
        }
        
        
        $data = array(
                      'user_id'=>$user_id,
                      'feedback_content'=>$feedback_content,
                      'feedback_time'=>date("Y-m-d H:i:s"),
                      );
        
        $result = $this->feedback_model->insert_feedback($data);
        
        echo json_encode(array('msg' => 'insert feedback ok',
                               'status' => 'ok'));
    }

  
	
	
}