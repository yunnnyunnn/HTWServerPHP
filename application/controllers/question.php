<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Question extends My_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('question_model');
        $this->load->model('user_model');
        $this->load->model('device_model');
        $this->load->model('push_queue_ios_model');
		$this->load->model('push_queue_android_model');
		$this->load->model('answer_model');
		$this->load->model('answer_scores_model');
		$this->load->model('location_log_model');
        $this->load->model('notification_model');
        $this->load->library('geolocation');
        $this->load->library('payload_maker');
        $this->load->library('wp_push_notification_maker');
 		$this->load->library('android_push_notification_maker');

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
    
    
    
    
	public function get_question()
	{
		//echo date("Y-m-d H:i:s").'<br/>';
        $user_id = $this->user_id;
		$status = '';
		$msg = '';
		$question_rows = '';
		$question_time_limit = $this->input->post('question_time',TRUE);
		$time = date('Y-m-d H:i:s', strtotime($question_time_limit));
		$where = array(
			'question_time >' => $time,	
		);
        
        
        
        // 如果有傳區域限制
        $question_latitude_max = $this->input->post('question_latitude_max', TRUE);
        $question_latitude_min = $this->input->post('question_latitude_min', TRUE);
        $question_longitude_max = $this->input->post('question_longitude_max', TRUE);
        $question_longitude_min = $this->input->post('question_longitude_min', TRUE);
        if(isset($_POST["question_latitude_max"]) && isset($_POST["question_latitude_min"]) && isset($_POST["question_longitude_max"]) && isset($_POST["question_longitude_min"]))
        {
            
            $where['question_latitude <='] = $question_latitude_max;
            $where['question_latitude >='] = $question_latitude_min;
            $where['question_longitude <='] = $question_longitude_max;
            $where['question_longitude >='] = $question_longitude_min;
        }
        
        // 如果有指定id
        
        $question_id = $this->input->post('question_id', TRUE);
        if(isset($_POST["question_id"]))
        {
            
            $where['question_id'] = $question_id;
            
        }
        
        // if want question by user

        if(isset($_POST["question_user_id"]))
        {
            
            $question_user_id = $this->input->post('question_user_id', TRUE);
            $where['question.user_id'] = $question_user_id;
            
        }
        
		$field = array('question.*','user.user_nickname', 'timediff(question.question_time, now()) as question_timediff');
        $answer_field = array('answer.*','user.user_nickname', 'timediff(answer.answer_time, now()) as answer_timediff');
        $answer_scores_field = array('answer_scores.*', 'user.user_nickname','user.user_medal','user.user_id');
		$query = $this->question_model->get_question($field,$where);
		$count = $query->num_rows();
		if($count>0)
		{
			$status = 'ok';
			$msg = 'get question successfully.';
			$answer_where = array();
          
			$answer_scores_where = array();
			$question_rows = $query->result();
			foreach($question_rows as $row)
			{
				$answer_where['question_id'] = $row->question_id;
				$answer = $this->answer_model->get_answer($answer_field,$answer_where);
				$answer_row = $answer->result();
				foreach($answer_row as $ans)
				{
					$answer_scores_where['answer_id'] = $ans->answer_id;
					$answer_scores = $this->answer_scores_model->get_answer_scores($answer_scores_field,$answer_scores_where);
					$ans->answer_scores = $answer_scores->result();        
                    $answer_scores_where['answer_scores.user_id'] = $user_id;
                    $ans->is_user_accept_answer = $this->answer_scores_model->get_answer_scores_count($answer_scores_where);
				}
				$row->answer = $answer_row;
                $answer_where['is_best_answer'] = 1;
                $row->is_best_answer_set = $this->answer_model->get_answer_count($answer_where);
				$answer->free_result();
			}
		}
		else
		{
			$status = 'fail';
			$msg = 'no results.';
		}
		echo json_encode(array('status'=>$status,'msg' => $msg,'result' => $question_rows));
		
		/*
		echo '<br/>';

		$query = $this->question_model->get_question_with_answer($where);
		$count = $query->num_rows();
		$question_rows = $query->result_array();
		$result = array();
		$new_question = array();
		foreach($question_rows as $key => $row)
		{
			$question_id = $row['question_id'];
			if (array_key_exists($row['question_id'], $new_question))
			{
				$answer['answer_id'] = $row['answer_id'];
				$answer['answer_content'] = $row['answer_content'];
				$answer['answer_time'] = $row['answer_time'];
				$answer['answer_photo_url'] = $row['answer_photo_url'];
				$answer['answer_latitude'] = $row['answer_latitude'];
				$answer['answer_longitude'] = $row['answer_longitude'];
				$answer['is_best_answer'] = $row['is_best_answer'];
				$answer['user_id'] = $row['user_id'];
				$answer['answer_score'] = $row['answer_score'];
				$new_question[$question_id]['answer'][] = $answer;		
			}
			else
			{
				$new_question[$question_id] = $row;
				
				if(!is_null($row['answer_id'])){
					$answer['answer_id'] = $row['answer_id'];
					$answer['answer_content'] = $row['answer_content'];
					$answer['answer_time'] = $row['answer_time'];
					$answer['answer_photo_url'] = $row['answer_photo_url'];
					$answer['answer_latitude'] = $row['answer_latitude'];
					$answer['answer_longitude'] = $row['answer_longitude'];
					$answer['is_best_answer'] = $row['is_best_answer'];
					$answer['user_id'] = $row['user_id'];
					$answer['answer_score'] = $row['answer_score'];	
					$new_question[$question_id]['answer'][] = $answer;	
				}else
				{
					$new_question[$question_id]['answer']=array();
				}
				
				unset($new_question[$question_id]['answer_id']);
				unset($new_question[$question_id]['answer_content']);
				unset($new_question[$question_id]['answer_time']);
				unset($new_question[$question_id]['answer_photo_url']);
				unset($new_question[$question_id]['answer_latitude']);
				unset($new_question[$question_id]['answer_longitude']);
				unset($new_question[$question_id]['is_best_answer']);
				unset($new_question[$question_id]['user_id']);
				unset($new_question[$question_id]['answer_score']);
			}
			
			
		}
		$result[] =  $new_question;
		//print_r($result);
		echo '<br/>';
		echo json_encode(array('status'=>$status,'msg' => $msg,'count'=>$count,'result' => $result));
		*/
	}
	
    public function get_question_preview_on_map() {
        
        // get the request sender's user_id
        $user_id = $this->user_id;
        $where = array();
        
        // prevent from not sending post value
        if(!isset($_POST["screen_icon_width_ratio"]) OR !isset($_POST["screen_icon_height_ratio"]) OR !isset($_POST["latDelta"]) OR !isset($_POST["longDelta"]))
        {
            echo json_encode(array('msg' => 'post value not set',
                                   'status' => 'fail'));
            return;
        }
        $screen_icon_width_ratio = $this->input->post('screen_icon_width_ratio', TRUE);
        $screen_icon_height_ratio = $this->input->post('screen_icon_height_ratio', TRUE);
        $latDelta = $this->input->post('latDelta', TRUE);
        $longDelta = $this->input->post('longDelta', TRUE);
        
        // calculate the minimum distance which should be exist between each two shares
        $latDelta_minimum_distance = $latDelta/$screen_icon_width_ratio;
        $longDelta_minimum_distance = $longDelta/$screen_icon_height_ratio;
        
        // if there's a timelimit
        $question_time_limit = $this->input->post('question_time',TRUE);
        $time = date('Y-m-d H:i:s', strtotime($question_time_limit));
		$where = array(
                       'question_time >' => $time,
                       );
        
        // if there's area limit
        $question_latitude_max = $this->input->post('question_latitude_max', TRUE);
        $question_latitude_min = $this->input->post('question_latitude_min', TRUE);
        $question_longitude_max = $this->input->post('question_longitude_max', TRUE);
        $question_longitude_min = $this->input->post('question_longitude_min', TRUE);
        if(isset($_POST["question_latitude_max"]) && isset($_POST["question_latitude_min"]) && isset($_POST["question_longitude_max"]) && isset($_POST["question_longitude_min"]))
        {
            
            $where['question_latitude <='] = $question_latitude_max;
            $where['question_latitude >='] = $question_latitude_min;
            $where['question_longitude <='] = $question_longitude_max;
            $where['question_longitude >='] = $question_longitude_min;
        }
        
        $field = array('question.*','user.user_nickname', 'timediff(question.question_time, now()) as question_timediff');
        $query = $this->question_model->get_question($field,$where);
        
        $questions = $query->result();

        $questions_preview = array();
        
        
        foreach($questions as $question)
        {
            
            $found = FALSE;
            foreach($questions_preview as $stored_question)
            {
                $latDelta_between = abs($question->question_latitude - $stored_question->question_latitude);
                $longDelta_between = abs($question->question_longitude - $stored_question->question_longitude);
                
                if ($latDelta_between < $latDelta_minimum_distance && $longDelta_between < $longDelta_minimum_distance)
                {
                    $found = TRUE;
                    $stored_question->child_questions[] = $question->question_id;
                    break;
                }
                
            }
            
            if(!$found){
                $question->child_questions = array();
                $questions_preview[] = $question;
            }
        }
        
        
        // send the final
        echo json_encode(array('constraints' => $where,
                               'result' => $questions_preview,
                               'msg' => 'get question preview on map ok',
                               'status' => 'success'
                               ));
        
        #####start from here######

        
    }
    
    public function get_question_preview_by_user() {
        
        $user_id = $this->user_id;
		$status = '';
		$msg = '';

        $where = array(
        'question.user_id' => $user_id
        );
        
        $field = array('question.*','user.user_nickname', 'timediff(question.question_time, now()) as question_timediff', '(select count(*) from answer where answer.question_id = question.question_id) as answer_count', 'exists(select * from answer where answer.question_id = question.question_id and answer.is_best_answer = 1) as has_best_answer');
		$query = $this->question_model->get_question($field,$where);

        $count = $query->num_rows();
        
        
		if($count>0)
		{
			$status = 'ok';
			$msg = 'get question preview by user successfully.';
            $question_rows = $query->result();

		}
		else
		{
			$status = 'ok';
			$msg = 'no results.';
            
            $question_rows = '';
            
		}
		echo json_encode(array('status'=>$status,'msg' => $msg,'result' => $question_rows));
        
    }
    
    public function get_specific_questions() 
	{
        $user_id = $this->user_id;
		$status = 'success';
		$msg = 'get question ok';
		$qid_array = array();
		$question_id_json = $this->input->post('question_id_json', TRUE);
	 	if(isset($_POST["question_id_json"]))
		{
			$qid_array = json_decode($question_id_json,TRUE);
		}
		$questions = array();
        $field = array('question.*','user.user_nickname', 'timediff(question.question_time, now()) as question_timediff');
        $answer_field = array('answer.*','user.user_nickname', 'timediff(answer.answer_time, now()) as answer_timediff');
        $answer_scores_field = array('answer_scores.*', 'user.user_nickname','user.user_medal','user.user_id');
		foreach($qid_array as $specific)
		{
			$where = array();
			$where['question_id'] = $specific['question_id'];
			
			$query = $this->question_model->get_question($field,$where);
			$count = $query->num_rows();
			if($count>0)
			{
				$one_question = $query->row();
				$question_id = $one_question->question_id;
				$answer_where = array('question_id'=>$question_id);
					
				$answer = $this->answer_model->get_answer($answer_field,$answer_where);	
				$answer_row = $answer->result();
				foreach($answer_row as $ans)
				{
					$answer_scores_where = array('answer_id'=>$ans->answer_id);
					$answer_scores = $this->answer_scores_model->get_answer_scores($answer_scores_field,$answer_scores_where);
					$ans->answer_scores = $answer_scores->result();            
                    $answer_scores_where['answer_scores.user_id'] = $user_id;
                    $ans->is_user_accept_answer = $this->answer_scores_model->get_answer_scores_count($answer_scores_where);
				}
				$one_question->answer = $answer_row;
                $answer_where['is_best_answer'] = 1;
                $one_question->is_best_answer_set = $this->answer_model->get_answer_count($answer_where);
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
    
    
    public function get_user_around_question()
    {
        $question_latitude = $this->input->post('question_latitude',TRUE);
		$question_longitude = $this->input->post('question_longitude',TRUE);
        $question_distance_limited = $this->input->post('question_distance_limited',TRUE); // 手機端先寫死，傳15000
        $question_notification_time = $this->input->post('question_notification_time', TRUE); // 手機端先寫死，傳-1 days
        
        // 假如任何一個值是空的就無法執行
        if (empty($question_latitude)||empty($question_longitude)||empty($question_distance_limited)||empty($question_notification_time)) {
            $status = 'fail';
			$msg = 'miss post value';
            echo json_encode(array('status' => $status , 'msg' => $msg));
            return;
        }
        
        $question_notification_time = date('Y-m-d H:i:s', strtotime($question_notification_time));
        $where = array(
            'location_log_time >=' => $question_notification_time
        );
        $query = $this->location_log_model->get_location_log($where);
        
        if ($query->num_rows() == 0) {
            $status = 'ok';
			$msg = 'no user location data within the time';
            echo json_encode(array('status' => $status , 'msg' => $msg));
            return;
        }
        
        $possible_location = $query->result();
        $available_notification_receiver = array();
        foreach ($possible_location as $single_location) {
            
            $is_around_question = FALSE;
            // 計算問題與這個location data之間的距離
            $distance_between_question_and_user = $this->geolocation->vincentyGreatCircleDistance($question_latitude, $question_longitude, $single_location->location_latitude, $single_location->location_longitude);
            if ($distance_between_question_and_user <= $question_distance_limited) {
                $is_around_question = TRUE;
            }
            
            $is_Already_receiver = FALSE;
            foreach ($available_notification_receiver as $receiverData) {
                if ($receiverData->user_id==$single_location->user_id) {
                    $is_Already_receiver = TRUE;
                    break;
                }
            }
            
            if ($is_around_question&&!$is_Already_receiver&&$single_location->user_id!=$this->user_id) {
                

                $available_notification_receiver[] = $single_location;
            }
        }
        
        $status = 'ok';
        $msg = 'get user around question ok';
        
		echo json_encode(array('status'=>$status,'msg' => $msg,'result' => $available_notification_receiver));
        
        
    }
    
    public function get_user_around_question_with_answer_rate()
    {
        $question_latitude = $this->input->post('question_latitude',TRUE);
		$question_longitude = $this->input->post('question_longitude',TRUE);
        $question_distance_limited = $this->input->post('question_distance_limited',TRUE); // 手機端先寫死，傳15000
        $question_notification_time = $this->input->post('question_notification_time', TRUE); // 手機端先寫死，傳-1 days
        
        // 假如任何一個值是空的就無法執行
        if (empty($question_latitude)||empty($question_longitude)||empty($question_distance_limited)||empty($question_notification_time)) {
            $status = 'fail';
			$msg = 'miss post value';
            echo json_encode(array('status' => $status , 'msg' => $msg));
            return;
        }
        
        $question_notification_time = date('Y-m-d H:i:s', strtotime($question_notification_time));
        $where = array(
                       'location_log_time >=' => $question_notification_time
                       );
        $query = $this->location_log_model->get_location_log($where);
        
        if ($query->num_rows() == 0) {
            $status = 'ok';
			$msg = 'no user location data within the time';
            echo json_encode(array('status' => $status , 'msg' => $msg));
            return;
        }
        
        $possible_location = $query->result();
        $available_notification_receiver = array();
        $available_user_id = array();
        
        foreach ($possible_location as $single_location) {
            
            $is_around_question = FALSE;
            // 計算問題與這個location data之間的距離
            $distance_between_question_and_user = $this->geolocation->vincentyGreatCircleDistance($question_latitude, $question_longitude, $single_location->location_latitude, $single_location->location_longitude);
            if ($distance_between_question_and_user <= $question_distance_limited) {
                $is_around_question = TRUE;
            }
            
            $is_Already_receiver = FALSE;
            foreach ($available_notification_receiver as $receiverData) {
                if ($receiverData->user_id==$single_location->user_id) {
                    $is_Already_receiver = TRUE;
                    break;
                }
            }
            
            if ($is_around_question&&!$is_Already_receiver&&$single_location->user_id!=$this->user_id) {
                
                
                $available_notification_receiver[] = $single_location;
                
                $available_user_id[] = $single_location->user_id;
            }
        }
        
        /*
        //$available_user_id = array(1379545893, 47100); test only
        
        $answer_rate_data = $this->notification_model->get_notification_with_answer($available_user_id)->result();
        
        //print_r($answer_rate_data);
        
        $answer_rate_data = $answer_rate_data[0];
        
        $answer_rate = $answer_rate_data->total_answer/$answer_rate_data->total_notification;
        */
        
        $answer_rate = $this->answer_model->get_total_answer_rate()->result();
        $answer_rate = $answer_rate[0];
        
        $user_coins = $this->user_model->get_user('user_money', array('user_id'=>$this->user_id))->result();
        $user_coins = $user_coins[0];
        
        $status = 'ok';
        $msg = 'get user around question ok';
        
		echo json_encode(array('status'=>$status,'msg' => $msg,'result' => array('available_notification_receiver' =>count($available_notification_receiver), 'answer_rate' => $answer_rate->answer_rate, 'user_coins' => $user_coins->user_money)));
        
        
    }
    
    
	public function insert_question()
	{
		$status = '';
		$msg = '';
		//$user_email = $session['user_email'];
		//$user_id = $this->input->post('user_id',TRUE);
		$user_email = $this->user_email;
		$user_id = $this->user_id;
		$question_latitude = $this->input->post('question_latitude',TRUE);
		$question_longitude = $this->input->post('question_longitude',TRUE);
		$question_time = date("Y-m-d H:i:s");
		$question_content = $this->input->post('question_content',TRUE);
		$question_distance_limited = $this->input->post('question_distance_limited',TRUE);
		$question_is_photo_needed = $this->input->post('question_is_photo_needed',TRUE);
		$question_time_left = $this->input->post('question_time_left',TRUE);
		$question_reward = $this->input->post('question_reward',TRUE);
		
		$is_pay = $this->input->post('is_pay',TRUE);
        $question_notification_time = $this->input->post('question_notification_time', TRUE); // 手機端先寫死，傳-1 days
		
		/*
		$user_id = rand (1,3);
		$question_latitude = rand (-90,90).'.'.rand (1000,9999);
		$question_longitude = rand (-180,180).'.'.rand (1000,9999);
		//$question_time = date("Y-m-d H:i:s");
		$question_time = $time = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) - (60 * 60 * rand (1,720)));
		$question_content = iconv('UTF-8', 'BIG5//TRANSLIT//IGNORE',$this->getRandomString(rand (1,50)));
		$question_distance_limited = rand (1,20);
		$question_is_photo_needed = 0;
		$question_time_left = rand (1,24);
		$question_reward = rand (5,20);
		
		$is_pay = 0;
		$limit_min = 9000;
		*/
		if(!empty($question_content)||!empty($question_reward))
		{
			$data = array(
				'user_id' => $user_id,
				'question_latitude' => $question_latitude,
				'question_longitude' => $question_longitude,
				'question_time' => $question_time,
				'question_content' => $question_content,
				'question_distance_limited' => $question_distance_limited,
				'question_is_photo_needed' => $question_is_photo_needed,
				'question_time_left' => $question_time_left,
				'question_reward' => $question_reward,
			);
            
            
            if($is_pay) // 要通知人，檢查下是否有足夠的錢
            {
                if (!$this->user_money_enough_checker($user_id, $this->current_payment_per_question)) {
                    $status = 'fail';
                    $msg = 'money not enough';
                    echo json_encode(array('status' => $status , 'msg' => $msg));
                    return;
                }
            }
            
            
            $question_id = $this->question_model->insert_question($data);
			
			if(isset($question_id))
			{

				$status = 'ok';
				$msg = 'Question insert sucessfully.';
				if($is_pay) // 要通知人
				{
                    
                    
                    
                    
                    // 假如任何一個值是空的就無法執行
                    if (empty($question_latitude)||empty($question_longitude)||empty($question_distance_limited)||empty($question_notification_time)) {
                        $status = 'ok';
                        $msg = 'Question insert sucessfully but miss post value so cant notify';
                        echo json_encode(array('status' => $status , 'msg' => $msg));
                        return;
                    }
                    
                    $question_notification_time = date('Y-m-d H:i:s', strtotime($question_notification_time));
                    $where = array(
                        'location_log_time >=' => $question_notification_time
                                   );
                    $query = $this->location_log_model->get_location_log($where);
                    
                    if ($query->num_rows() == 0) {
                        $status = 'ok';
                        $msg = 'Question insert sucessfully but no user location data within the time';
                        echo json_encode(array('status' => $status , 'msg' => $msg));
                        return;
                    }
                    
                    $possible_location = $query->result();
                    $available_notification_receiver = array();
                    foreach ($possible_location as $single_location) {
                        
                        $is_around_question = FALSE;
                        // 計算問題與這個location data之間的距離
                        $distance_between_question_and_user = $this->geolocation->vincentyGreatCircleDistance($question_latitude, $question_longitude, $single_location->location_latitude, $single_location->location_longitude);
                        if ($distance_between_question_and_user <= $question_distance_limited) {
                            $is_around_question = TRUE;
                        }
                        
                        if ($is_around_question&&!in_array($single_location->user_id, $available_notification_receiver)&&$single_location->user_id!=$this->user_id) {
                            $available_notification_receiver[] = $single_location->user_id;
                        }
                    }
                    
                    if (count($available_notification_receiver)==0) {
                        $status = 'ok';
                        $msg = 'Question insert sucessfully but no user to notify';
                        echo json_encode(array('status' => $status , 'msg' => $msg));
                        return;
                    }
                    
                    
                    if (!$this->user_pay_money($user_id, $this->current_payment_per_question)) {
                        $status = 'ok';
                        $msg = 'fail to pay, Question insert sucessfully but no notification';
                        echo json_encode(array('status' => $status , 'msg' => $msg));
                        return;
                    }
                    
                    // 開始制作通知
					$notification_id = '';
                    foreach ($available_notification_receiver as $receiver) {
                        $data = array (
                                       'user_id_sender' => $user_id,
                                       'user_id_receiver' => $receiver,
                                       'notification_type' => 5,
                                       'post_id' => $question_id,
                                       'notification_time' => date("Y-m-d H:i:s"),
                                       'notification_is_record' => 0,
                                       );
                        
                        $notification_id = $this->notification_model->insert_notification($data);
                        
                    }
                    
                    
                    
                    
                    /////////////////////////////////
                    // 取得所有的device token
                    $device_token_array = $this->get_device_token($available_notification_receiver);
                    

                    
                    
                    
                    $where = array (
                                    'user_id'=> $user_id
                                    );
                    
                    $query_user = $this->user_model->get_user('*', $where);
                    $user_nickname = $query_user->row()->user_nickname;
                    // 開始製作推播db
                    foreach ($device_token_array as $device_token) {
                        if ($device_token['device_type'] == 1) { // iOS推播
                            $loc_args = array($user_nickname);
                            $payload = $this->payload_maker->make_payload('PUSH_MESSAGE_TYPE_5', $loc_args, $question_id);
                            if (strlen($payload) <= 256)
                            {
                                $data  = array(
                                               'pqo_device_token' => $device_token['device_token'],
                                               'pqi_payload' => $payload,
                                               'pqi_time_queued' => date("Y-m-d H:i:s")
                                               );
                                $result = $this->push_queue_ios_model->insert_push_queue_iOS($data);
                            }
                            
                        }
                        else if ($device_token['device_type'] == 3) { // windows phone推播
                            
                            $result= $this->wp_push_notification_maker->send_toast('Howeather' ,$user_nickname.'wants to know your local weather','/question_detail_page.xaml?question_id='.$question_id.'' ,2,$device_token['device_token']);
                           
                            
                        }
                        else if ($device_token['device_type'] == 2) { // android推播
                            $payload = $this->android_push_notification_maker->make_payload('5', $question_id, $user_nickname,$notification_id);
							$data  = array(
										   'registration_id' => $device_token['device_token'],
										   'pqa_payload' => $payload,
										   'pqa_time_queued' => date("Y-m-d H:i:s")
										   );
							$result = $this->push_queue_android_model->insert_push_queue_android($data);
                        }
                        
                    }
                    
                    
                    
                    $status = 'ok';
                    $msg = 'Pay success, Question insert sucessfully and notify some users';
                    
                    echo json_encode(array('status' => $status , 'msg' => $msg, 'result' => $available_notification_receiver));
                    return;

				}
			}
			else
			{
				$status = 'fail';
				$msg = 'Question insert Databse error.';
			}		
		}
		else
		{
			$status = 'fail';
			$msg = 'miss post value';
		}
		echo json_encode(array('status' => $status , 'msg' => $msg));
	}
	
	function insert_answer()
	{
		$user_email = $this->user_email;
		$user_id = $this->user_id;
		$question_id = $this->input->post('question_id',TRUE);
		$answer_latitude = $this->input->post('answer_latitude',TRUE);
		$answer_longitude = $this->input->post('answer_longitude',TRUE);
		$answer_time = date("Y-m-d H:i:s");
		$answer_photo_url = $this->input->post('answer_photo_url',TRUE);
		$answer_content	= $this->input->post('answer_content',TRUE);
		$is_best_answer = 0;//$this->input->post('is_best_answer',TRUE);
		$answer_score = 0;//$this->input->post('answer_score',TRUE);
		
		$file_name = '';   
          
		/*
		$user_id = rand(1,3);
		$question_id = rand(1,1000);
		$answer_latitude = rand (-90,90).'.'.rand (1000,9999);
		$answer_longitude = rand (-180,180).'.'.rand (1000,9999);;
		$answer_time = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) - (60  * rand (1,30)));
		$answer_photo_url = '';
		$answer_content	= iconv('UTF-8', 'BIG5//TRANSLIT//IGNORE',$this->getRandomString(rand (1,50)));
		$is_best_answer = 0;
		$answer_score = 0;//$this->input->post('answer_score',TRUE);
		*/
		$answer_id = NULL;
		if(!empty($answer_content))
		{
			if(isset($_FILES['theFile']))
			{
				$this->load->library('S3');
				$file_name = "$user_id/".time().".jpg";
				if (!$this->s3->putObjectFile($_FILES['theFile']['tmp_name'], "weather_bucket", $file_name, S3::ACL_PUBLIC_READ)) {
					$status = 'fail';
					$msg = "Something went wrong while uploading your file... sorry.";
					echo json_encode(array('status' => $status , 'msg' => $msg));
					return;
				}
			}
			
			$data = array(
				'user_id' => $user_id,
				'question_id' => $question_id,
				'answer_latitude' => $answer_latitude,
				'answer_longitude' => $answer_longitude,
				'answer_time' => $answer_time,
				'answer_content' => $answer_content,
				'answer_photo_url' => $answer_photo_url,
				'is_best_answer' => $is_best_answer,
				'answer_score' => $answer_score,
				'answer_photo_url' => $file_name
			);
			$answer_id = $this->answer_model->insert_answer($data);

			///////////增加使用者經驗值
			if($file_name!='') {
                 $new_exp = $this->update_user_exp($user_id,$this->answer_question_with_photo);
                // 這邊開始檢視需不需要給他新的medal
                $this->check_and_insert_user_medal($user_id, $new_exp);
            }
            else {
                $new_exp = $this->update_user_exp($user_id,$this->answer_question);
                // 這邊開始檢視需不需要給他新的medal
                $this->check_and_insert_user_medal($user_id, $new_exp);
            }
            

			if(isset($answer_id))
			{
                
                
				$status = 'ok';
				$msg = 'Question insert sucessfully.';
				//notification
                
                
                // 開始制作一個通知
                // 先抓到要傳給哪些人
                
                $where = array(
                               'question_id' => $question_id
                               );
                $field = array('question.user_id');
                
                $receiver_array = array();
                
                // 抓到發問者
                $query = $this->question_model->get_question($field,$where);
                $query_result = $query->result();
                if ($query->num_rows() > 0) {
                    foreach ($query_result as $single_question) {
                        if (!in_array($single_question->user_id, $receiver_array)&&($single_question->user_id!=$user_id))
                        {
                            $receiver_array[] = $single_question->user_id;
                        }
                    }
                }
                
                // 開始制作通知
				$notification_id = '';
                foreach ($receiver_array as $receiver) {
                    $data = array (
                                   'user_id_sender' => $user_id,
                                   'user_id_receiver' => $receiver,
                                   'notification_type' => 2,
                                   'post_id' => $question_id,
                                   'notification_time' => date("Y-m-d H:i:s"),
                                   'notification_is_record' => 0,
                                   );
                    
                    $notification_id = $this->notification_model->insert_notification($data);
                    
                }
                
                
                
                /////////////////////////////////
                
                // 取得所有的device token
                $device_token_array = $this->get_device_token($receiver_array);
                
                
                
                $where = array (
                                'user_id'=> $user_id
                                );
                
                $query_user = $this->user_model->get_user('*', $where);
                $user_nickname = $query_user->row()->user_nickname;
                // 開始製作推播db
                foreach ($device_token_array as $device_token) {
                    if ($device_token['device_type'] == 1) { // iOS推播
                        $loc_args = array($user_nickname);
                        $payload = $this->payload_maker->make_payload('PUSH_MESSAGE_TYPE_2', $loc_args, $question_id);
                        if (strlen($payload) <= 256)
                        {
                            $data  = array(
                                           'pqo_device_token' => $device_token['device_token'],
                                           'pqi_payload' => $payload,
                                           'pqi_time_queued' => date("Y-m-d H:i:s")
                                           );
                            $result = $this->push_queue_ios_model->insert_push_queue_iOS($data);
                        }
                        
                    }
                    else if ($device_token['device_type'] == 3) { // windows phone推播
                        
                        $result= $this->wp_push_notification_maker->send_toast('Howeahter' ,$user_nickname.'answered your question!','/question_detail_page.xaml?question_id='.$question_id.'' ,2,$device_token['device_token']);
                        
                        
                    }
                    else if ($device_token['device_type'] == 2) { // android推播        
                        $payload = $this->android_push_notification_maker->make_payload('2', $question_id, $user_nickname,$notification_id);
						$data  = array(
							'registration_id' => $device_token['device_token'],
							'pqa_payload' => $payload,
							'pqa_time_queued' => date("Y-m-d H:i:s")
						);
						$result = $this->push_queue_android_model->insert_push_queue_android($data);   
                    }
                    
                }

                
                
                
			}
			else
			{
				$status = 'fail';
				$msg = 'Answer insert : Databse error.';
			}
		}
		else
		{
			$status = 'fail';
			$msg = 'miss post value';
		}
		echo json_encode(array('status' => $status , 'msg' => $msg , 'answer_id' => $answer_id,'answer_photo_url'=>$file_name));
	}
	function set_best_answer()
	{
		$status = '';
		$msg = '';
		$answer_id = $this->input->post('answer_id',TRUE);
		if(isset($answer_id)&&!empty($answer_id)&&is_numeric($answer_id))
		{	
			// detect is best answer set ?
			$where = array('answer_id' => $answer_id);
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
			// end detect
			$where = array('answer_id' => $answer_id);
			$data = array('is_best_answer' => 1);
			if($this->answer_model->update_answer($where,$data))
			{
				$status = 'ok';
				$msg = 'Set bset answer Successfully.';
                
                
                

                
                // 開始制作一個通知
                
                // 抓到question_id和回答者id
                $question_id;
                $receiver;
                $where = array(
                               'answer_id' => $answer_id
                               );
                $field = array('answer.user_id', 'answer.question_id');
                $answer = $this->answer_model->get_answer($field,$where);
				$answer_row = $answer->result();
                if ($answer->num_rows() > 0) {
                    foreach($answer_row as $ans)
                    {
                        $question_id = $ans->question_id;
                        $receiver = $ans->user_id;
                    }
                }
                
                //增加使用者經驗值
                $new_exp = $this->update_user_exp($receiver,$this->answer_is_best_answer);
                // 這邊開始檢視需不需要給他新的medal
                $this->check_and_insert_user_medal($receiver, $new_exp);
                

                // 抓到發問者
                $sender;
                $where = array(
                               'question_id' => $question_id
                               );
                $field = array('question.user_id');
                
                $query = $this->question_model->get_question($field,$where);
                $query_result = $query->result();
                if ($query->num_rows() > 0) {
                    foreach ($query_result as $single_question) {
                        $sender = $single_question->user_id;
                    }
                }
                
                // 開始制作通知
				$notification_id = '';
                if ($sender&&$receiver&&$question_id) {
                    $data = array (
                                   'user_id_sender' => $sender,
                                   'user_id_receiver' => $receiver,
                                   'notification_type' => 3,
                                   'post_id' => $question_id,
                                   'notification_time' => date("Y-m-d H:i:s"),
                                   'notification_is_record' => 0,
                                   );
                    
                    $notification_id = $this->notification_model->insert_notification($data);
                }
                
                
                
                
                
                /////////////////////////////////
                // 取得所有的device token
                $receiver_array = array($receiver);
                $device_token_array = $this->get_device_token($receiver_array);
                
                
                $where = array (
                                'user_id'=> $sender
                                );
                
                $query_user = $this->user_model->get_user('*', $where);
                $user_nickname = $query_user->row()->user_nickname;
                // 開始製作推播db
                foreach ($device_token_array as $device_token) {
                    if ($device_token['device_type'] == 1) { // iOS推播
                        $loc_args = array($user_nickname);
                        $payload = $this->payload_maker->make_payload('PUSH_MESSAGE_TYPE_3', $loc_args, $question_id);
                        if (strlen($payload) <= 256)
                        {
                            $data  = array(
                                           'pqo_device_token' => $device_token['device_token'],
                                           'pqi_payload' => $payload,
                                           'pqi_time_queued' => date("Y-m-d H:i:s")
                                           );
                            $result = $this->push_queue_ios_model->insert_push_queue_iOS($data);
                        }
                        
                    }
                    else if ($device_token['device_type'] == 3) { // windows phone推播
                        
                          $result= $this->wp_push_notification_maker->send_toast('Howeather' ,$user_nickname.'chose your answer as the best answer!','/question_detail_page.xaml?question_id='.$question_id.'' ,2,$device_token['device_token']);
                       
                        
                    }
                    else if ($device_token['device_type'] == 2) { // android推播
						$payload = $this->android_push_notification_maker->make_payload('3', $question_id, $user_nickname,$notification_id);
						$data  = array(
							'registration_id' => $device_token['device_token'],
							'pqa_payload' => $payload,
							'pqa_time_queued' => date("Y-m-d H:i:s")
						);
						$result = $this->push_queue_android_model->insert_push_queue_android($data);   
                    }
                    
                }
                
                
                
                
			}
			else
			{
				$status = 'fail';
				$msg = 'Update answer : Database Error';
			}
		}
		else
		{
			$status = 'fail';
			$msg = 'Missing answer id';
		}
		echo json_encode(array('status' => $status , 'msg' => $msg));
	}
	
	public function insert_answer_scores()
	{
		$status = '';
		$msg = '';
		$user_id = $this->user_id;
		$answer_id = $this->input->post('answer_id',TRUE);
		$scores = $this->input->post('scores',TRUE);
		if(!is_numeric($answer_id)||!is_numeric($scores))
		{
			$status = 'fail';
			$msg = 'ID error';
		}
		else
		{
			if($scores == 1||$scores == -1)
			{
				$data = array(
					'user_id' => $user_id,
					'answer_id' => $answer_id,
					'scores' => $scores
				);
				$update = FALSE;
				$field = array('answer_scores_id');
				$where = array('answer_scores.user_id' => $user_id,'answer_id' => $answer_id);
				$query = $this->answer_scores_model->get_answer_scores($field , $where);
				if($query->num_rows()>0)
				{
					if($this->answer_scores_model->delete_answer_scores($where))
					{
						//減少使用者經驗值
                        $this->update_user_exp($user_id,-$this->answer_is_liked);
                        
                        
                        ////////////////////刪除通知
                        
                        // 抓到question_id和回答者id
                        $question_id;
                        $receiver;
                        $where = array(
                                       'answer_id' => $answer_id
                                       );
                        $field = array('answer.user_id', 'answer.question_id');
                        $answer = $this->answer_model->get_answer($field,$where);
                        $answer_row = $answer->result();
                        if ($answer->num_rows() > 0) {
                            foreach($answer_row as $ans)
                            {
                                $question_id = $ans->question_id;
                                $receiver = $ans->user_id;
                            }
                        }
                        
                        $where = array(
                                       'user_id_sender'=>$user_id,
                                       'post_id'=>$question_id,
                                       'notification_type'=> 4
                                       );
                        if($this->notification_model->delete_notification($where)) {
                            $msg = 'delete answer score ok, delete notification success';

                        }
                        else {
                            $msg = 'delete answer score ok, but delete notification failed';

                        }
                        
                        
						$status = 'ok';
						$update = TRUE;
					}
					else
					{
						$status = 'fail';
						$msg = 'Update Score : Database Error';
						$update = FALSE;
					}
				}
				else
				{
					if($this->answer_scores_model->insert_answer_scores($data))
					{
						$status = 'ok';
						$msg = 'Insert Score Successfully.';
						$update = TRUE;
                        
                        // 增加使用者經驗值
                        $new_exp = $this->update_user_exp($user_id,$this->answer_is_liked);
                        // 這邊開始檢視需不需要給他新的medal
                        $this->check_and_insert_user_medal($user_id, $new_exp);
                        
                        
                        // 開始制作一個通知
                        
                        // 抓到question_id和回答者id
                        $question_id;
                        $receiver;
                        $where = array(
                                       'answer_id' => $answer_id
                                       );
                        $field = array('answer.user_id', 'answer.question_id');
                        $answer = $this->answer_model->get_answer($field,$where);
                        $answer_row = $answer->result();
                        if ($answer->num_rows() > 0) {
                            foreach($answer_row as $ans)
                            {
                                $question_id = $ans->question_id;
                                $receiver = $ans->user_id;
                            }
                        }
                        
                        // 開始制作通知
						$notification_id = '';
                        if ($receiver&&$question_id&&$receiver!=$user_id) {
                            $data = array (
                                           'user_id_sender' => $user_id,
                                           'user_id_receiver' => $receiver,
                                           'notification_type' => 4,
                                           'post_id' => $question_id,
                                           'notification_time' => date("Y-m-d H:i:s"),
                                           'notification_is_record' => 0,
                                           );
                            
                            $notification_id = $this->notification_model->insert_notification($data);
                        }
                        
                        
                        
                        /////////////////////////////////
                        // 取得所有的device token
                        $receiver_array = array($receiver);
                        $device_token_array = $this->get_device_token($receiver_array);
                        
                        
                        
                        $where = array (
                                        'user_id'=> $user_id
                                        );
                        
                        $query_user = $this->user_model->get_user('*', $where);
                        $user_nickname = $query_user->row()->user_nickname;
                        // 開始製作推播db
                        foreach ($device_token_array as $device_token) {
                            if ($device_token['device_type'] == 1) { // iOS推播
                                $loc_args = array($user_nickname);
                                $payload = $this->payload_maker->make_payload('PUSH_MESSAGE_TYPE_4', $loc_args, $question_id);
                                if (strlen($payload) <= 256)
                                {
                                    $data  = array(
                                                   'pqo_device_token' => $device_token['device_token'],
                                                   'pqi_payload' => $payload,
                                                   'pqi_time_queued' => date("Y-m-d H:i:s")
                                                   );
                                    $result = $this->push_queue_ios_model->insert_push_queue_iOS($data);
                                }
                                
                            }
                            else if ($device_token['device_type'] == 3) { // windows phone推播
                                
                                $result= $this->wp_push_notification_maker->send_toast('Howeather' ,$user_nickname.'agreed on your answer!','/question_detail_page.xaml?question_id='.$question_id.'' ,2,$device_token['device_token']);
                               
                                
                            }
                            else if ($device_token['device_type'] == 2) { // android推播
                                $payload = $this->android_push_notification_maker->make_payload('4', $question_id, $user_nickname,$notification_id);
								$data  = array(
									'registration_id' => $device_token['device_token'],
									'pqa_payload' => $payload,
									'pqa_time_queued' => date("Y-m-d H:i:s")
								);
								$result = $this->push_queue_android_model->insert_push_queue_android($data); 
                            }
                            
                        }
                        
                        
					}
					else
					{
						$status = 'fail';
						$msg = 'Insert Score : Database Error';
						$update = FALSE;
					}
				}
				if($update)
				{
					$field1 = array('SUM(scores) AS total_scores');
					$where1 = array(
						'answer_id' => $answer_id,
					);
					$query = $this->answer_scores_model->get_answer_scores($field1 , $where1);
					if($query->num_rows()>0)
					{
						$answer_score = $query->row()->total_scores;
						$answer_data = array(
							'answer_score' => $answer_score	
						);
						$this->answer_model->update_answer($where1,$answer_data);
					}
				}
				else
				{
					$status = 'fail';
				}
			}
			else
			{
				$status = 'fail';
				$msg = 'Scores error';
			}
		}	
		echo json_encode(array('status' => $status , 'msg' => $msg));
	}
	
	function getRandomString($length = 6) 
	{
		$validCharacters = "abcdefghijklmnopqrstuxyvwzABCDEFGHIJKLMNOPQRSTUXYVWZ+-*#&@!?";
		$validCharNumber = strlen($validCharacters);
	 	
		$result = "";
	 
		for ($i = 0; $i < $length; $i++) {
			$index = mt_rand(0, $validCharNumber - 1);
			$result .= $validCharacters[$index];
		}
	 
		return $result;
	}

	
	
}