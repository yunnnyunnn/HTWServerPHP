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
        $payer_id = $this->input->post('payer_id', TRUE);
        $payment = $this->input->post('payment', TRUE);
        
        if ($this->user_pay_money($payer_id, $payment)) {
            echo 'success pay';
        }
        else {
            echo 'failed pay';

        }
        
    }

	
	
}