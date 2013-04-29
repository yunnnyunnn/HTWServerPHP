<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Question extends My_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('question_model');
		$this->load->model('answer_model');
		$this->load->model('answer_scores_model');
		$this->load->model('location_log_model');		
		
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
		$status = '';
		$msg = '';
		$question_rows = '';
		$question_time_limit = $this->input->post('question_time',TRUE);
		$time = date('Y-m-d H:i:s', strtotime($question_time_limit));
		$where = array(
			'question_time >' => $time,	
		);
		$field = array('question.*','user.user_nickname', 'timediff(question.question_time, now()) as question_timediff');
		$query = $this->question_model->get_question($field,$where);
		$count = $query->num_rows();
		if($count>0)
		{
			$status = 'ok';
			$msg = 'get question successfully.';
			$answer_where = array();
			$answer_field = array('answer.*','user.user_nickname', 'timediff(answer.answer_time, now()) as answer_timediff');
			$answer_scores_where = array();
			$answer_scores_field = array('*');
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
				}
				$row->answer = $answer_row;
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
		$limit_min = $this->input->post('limit_min',TRUE);
		
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
			
			if($this->question_model->insert_question($data))
			{

				$status = 'ok';
				$msg = 'Question insert sucessfully.';
				if($is_pay)
				{
					$this->load->library('geolocation');
					$time = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) - (60 * $limit_min));
					$query = $this->location_log_model->get_group_by_location_log($time,'user_id');
					echo 'count = '.$query->num_rows() .'<br/>';
					if($query->num_rows()>0)
					{
						foreach($query->result() as $row)
						{	
							$distance = $this->geolocation->get_distance($question_latitude,$question_longitude,$row->location_latitude,$row->location_longitude);
							echo 'distance = '.$distance.'<br/>';
							if($distance < $question_distance_limited)
							{
								echo 'user_id = '.$row->user_id;
								//notification
							}
						}
					}else
					{
						$msg = 'Question insert sucessfully,no user around the geolocation';
					}
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
				$file_name = $user_id.time().".jpg";
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
			if(isset($answer_id))
			{
				$status = 'ok';
				$msg = 'Question insert sucessfully.';
				//notification
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
			$where = array('answer_id' => $answer_id);
			$data = array('is_best_answer' => 1);
			if($this->answer_model->update_answer($where,$data))
			{
				$status = 'ok';
				$msg = 'Set bset answer Successfully.';
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
				$where = array('user_id' => $user_id,'answer_id' => $answer_id);
				$query = $this->answer_scores_model->get_answer_scores($field , $where);
				if($query->num_rows()>0)
				{
					if($this->answer_scores_model->delete_answer_scores($where))
					{
						$status = 'ok';
						$msg = 'Delete Score Successfully.';
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