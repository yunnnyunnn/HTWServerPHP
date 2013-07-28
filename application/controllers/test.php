<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends My_Controller {

	public function __construct()
	{
		parent::__construct();
        $this->load->library('image_manipulation');

	}
	public function index()
	{
		$value = $this->uri->segment(3);
		$user_email = $this->user_email;
		$user_id = $this->user_id;
		echo '<br/>'; 
		echo session_id();
		echo '<br/>'; 
		echo $user_id;
		echo '<br/>'; 
		echo $user_email;
		echo '<br/>'; 
		
		//echo json_encode(array('Hello'=>date("Y-m-d H:i:s"),'price' => QUESTION_PUSH_PRICE ));
	
	}
    
    public function testit()
    {
        $time = strtotime('2013-7-28 00:00:00');
        echo $time;
        $new_time = date('Y-m-d H:i:s', strtotime('+8 hours', $time));
        echo $new_time;
    }

	
	
}