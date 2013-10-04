<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
        $this->load->library('image_manipulation');
$this->load->model('notification_model');
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
    function get_notification_count()
	{
		$user_id = 1363440781;
        $where = array();
        $where['user_id_receiver']=$user_id;
        $notification_time = $this->input->post('notification_time', TRUE);
		//$notification_time = date('Y-m-d H:i:s');
        if(isset($_POST["notification_time"]))
        { 
            $where['notification_time >='] = $notification_time;
        }
		$count = $this->notification_model->get_notifitcation_count($where);
		echo json_encode(array('count' => $count));
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
	
	function test_image_compress()
	{
		$this->image_manipulation->create_thumbs(FCPATH.'userdata/1362455798/123.jpg',FCPATH.'userdata/1362455798/123_thumb.jpg',2000);
	}

}