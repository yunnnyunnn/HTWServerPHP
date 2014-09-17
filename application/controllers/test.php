<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		 $this->load->model('user_model');
        $this->load->library('image_manipulation');
        $this->load->library('rsa_util');
        $this->load->model('notification_model');
        $this->load->model('location_log_model');
        $this->load->model('share_model');
        $this->load->model('question_model');
        $this->load->model('answer_model');
        $this->load->model('device_model');
	    $this->load->model('answer_scores_model');
        $this->load->model('share_comment_model');
        $this->load->model('share_likes_model');
        $this->load->model('test_model');
        $this->load->library('mailer');
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
echo md5('4QxBn14Pjf172f16QtV7Q0lJ9SnX5m0j4gw6W5I1l33r3rIe0B44j680r',TRUE);
		//echo json_encode(array('Hello'=>date("Y-m-d H:i:s"),'price' => QUESTION_PUSH_PRICE ));

	}
    
    public function send_mail()
    {
        $to = 'success@simulator.amazonses.com';
        
        $subject = 'test mail';
        $body = 'hihi';
        $result = $this->mailer->send_mail($to,$subject,$body);
      if($result)
          echo 'success';
        else
            echo 'fail';
    }
    
    public function get_block_share()
    {
        $query = $this->share_model->get_block_share();
        print_r($query->result());
    }
        
    function rsa_test($data='default')
    {      
        echo $data;
        echo '<br/>'; 
        $encrypted = $this->rsa_util->public_encrypt($data);
        echo $encrypted;
        echo '<br/>'; 
        $decrypted = $this->rsa_util->priv_decrypt($encrypted);
        echo $decrypted;
        echo '<br/>'; 
    }
    
    function current_time()
    {
        echo json_encode($this->test_model->get_time()->row());
    }
    
    function get_device_token()
    {
        
        $receiver_array = array();
        $receiver_array[] = 37784;
        
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
        print_r($device_token_array);
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
	   public function get_latest_location()
	{
		//$user_id = $this->user_id;
		$user_id = '1';
        $where = array(
            'user_id' => $user_id
        );
        $query = $this->location_log_model->get_location_log($where,'*',1);
		if($query->num_rows()>0)
		{
			$status = 'ok';
		}
		else
		{
			$status = 'fail';
		}
		echo json_encode(array('status' => $status,'result' => $query->result()));
	}
	
	public function get_specific_shares() {
		  
		
		  $sid_array = array();
		  // 指定這次抓的串流從哪一篇開始
		  $share_id_json = $this->input->post('share_id_json', TRUE);
		 
		 // $share_id_json = '[{"share_id":1},{"share_id":2}]';
		  if(isset($_POST["share_id_json"]))
		  {
			  $sid_array = json_decode($share_id_json,true);
		  }
		 // print_r($sid_array);
		  
		  $shares = array();
		  foreach($sid_array as $specific)
		  {
			  $where = array();
			  $share_count = 1;
			  $where['share_id'] = $specific['share_id'];
			  
			  $field = array('share.*', 'timediff(share_time, now()) as share_timediff', 'user.user_nickname');		
			  $query = $this->share_model->get_share($where, $field, $share_count);	
			 
			  if($query->num_rows()>0)
			  {
				  $one_share = $query->row();
				  $share_id = $one_share->share_id;
				  $where_sub = array('share_id'=>$share_id);
				 
				  $field = array('share_comment.*', 'timediff(share_comment_time, now()) as share_comment_timediff', 'user.user_nickname');
				  
				  $query_comment = $this->share_comment_model->get_share_comment($where_sub, $field);
				  $one_share->share_comment = $query_comment->result();
  
				  $query_like = $this->share_likes_model->get_share_likes($where_sub);			
				  $one_share->share_likes = $query_like->result();
				
				  $shares[] = $one_share;
			  } 
			
			 
		  }
		  // 將最後結果送出
		  echo json_encode(array('result' => $shares,
								 'msg' => 'get share ok',
								 'status' => 'success'
								 ));
	  }
	   public function get_specific_questions() 
	{
		$status = 'success';
		$msg = 'get question ok';
		$qid_array = array();
		$question_id_json = $this->input->post('question_id_json', TRUE);
	 	if(isset($_POST["question_id_json"]))
		{
			$qid_array = json_decode($question_id_json,TRUE);
		}
		//print_r($qid_array);
		$questions = array();
	
		foreach($qid_array as $specific)
		{
			$where = array();
			$where['question_id'] = $specific['question_id'];
			$field = array('question.*','user.user_nickname', 'timediff(question.question_time, now()) as question_timediff');
			$query = $this->question_model->get_question($field,$where);
			$count = $query->num_rows();
			if($count>0)
			{
				$one_question = $query->row();
				$question_id = $one_question->question_id;
				$answer_where = array('question_id'=>$question_id);
				$answer_field = array('answer.*','user.user_nickname', 'timediff(answer.answer_time, now()) as answer_timediff');			
				$answer = $this->answer_model->get_answer($answer_field,$answer_where);	
				$answer_row = $answer->result();
				foreach($answer_row as $ans)
				{
					$answer_scores_where = array();
					$answer_scores_field = array('*');
					$answer_scores_where['answer_id'] = $ans->answer_id;
					$answer_scores = $this->answer_scores_model->get_answer_scores($answer_scores_field,$answer_scores_where);
					$ans->answer_scores = $answer_scores->result();
				}
				$one_question->answer = $answer_row;
				$answer->free_result();
				
				$questions[] = $one_question;
			}
			else
			{
				$status = 'fail';
				$msg = 'no results.';
			}
			
		}
	
		echo json_encode(array('status'=>$status,'msg' => $msg,'result' => $questions));
	}
function test_set_best_answer()
{
	 $answer_id = 1;
	$where = array('answer_id' => $answer_id);
			//
			$is_best_answer_set = FALSE;
			$one_answer = $this->answer_model->get_answer_without_user(array('question_id'),$where);
			if($one_answer->num_rows()>0)
			{
				$where = array('question_id' => $one_answer->row()->question_id);
				$answers = $this->answer_model->get_answer_without_user(array('answer_id','is_best_answer'),$where);
				
				foreach($answers->result() as $each_answer)
				{

					if($each_answer->is_best_answer == 1)
					{
						$is_best_answer_set = TRUE;
						break;
					}		 
				}
			}
			else
			{
				$status = 'fail';
				$msg = "the answer is not exist.";
				echo json_encode(array('status' => $status , 'msg' => $msg));
				return;
			}
			if($is_best_answer_set == TRUE)
			{
				$status = 'fail';
				$msg = "best answer is set.";
				echo json_encode(array('status' => $status , 'msg' => $msg));
				return;
			}
			echo "haha";
}
	
	function test_image_compress()
	{
		$this->image_manipulation->create_thumbs(FCPATH.'userdata/1362455798/123.jpg',FCPATH.'userdata/1362455798/123_thumb.jpg',2000);
	}

}