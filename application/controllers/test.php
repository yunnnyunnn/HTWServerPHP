<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		 $this->load->model('user_model');
        $this->load->library('image_manipulation');
$this->load->model('notification_model');
$this->load->model('location_log_model');
$this->load->model('share_model');
$this->load->model('answer_model');
            $this->load->model('share_comment_model');
            $this->load->model('share_likes_model');
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
		 
		  $share_id_json = '[{"share_id":1},{"share_id":2}]';
		  //if(isset($_POST["share_id_json"]))
		  //{
			  $sid_array = json_decode($share_id_json,true);
		  //}
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