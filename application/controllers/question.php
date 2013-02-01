<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Question extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
		$is_login = $this->session->userdata('user');
		if(!$is_login||empty($is_login['token']))
		{
			redirect('user');
		}
		else
		{
			$this->load->model('question_model');
			$this->load->model('answer_model');
			$this->load->model('location_log_model');
		}
		
	}
	public function index()
	{
		$value = $this->uri->segment(3);

		print_r($this->session->userdata('user'));	
		echo '<br/>'; 
		echo session_id();
		echo '<br/>'; 
		echo rand (1,3).'<br/>';
		echo rand (-90,90).'.'.rand (1000,9999).'<br/>';
		echo rand (-180,180).'.'.rand (1000,9999).'<br/>';
		$this->load->library('geolocation');
		echo json_encode(array('Hello'=>date("Y-m-d H:i:s"),'price' => QUESTION_PUSH_PRICE ));
	
	}
	public function get_question()
	{
		echo date("Y-m-d H:i:s").'<br/>';
		$status = '';
		$msg = '';
		$limit_hour = $this->input->post('limit_hour',true);
		$limit_hour = 79;
		$time = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) - (60 * 60 * $limit_hour));
		$where = array(
			'question_time >=' => $time,
		);
		$field = array('*');
		
		
		$query = $this->question_model->get_question($field,$where);
		//$count = $query->num_rows();
		$answer_where = array();
		$question_rows = $query->result();
		foreach($question_rows as $row)
		{
			$answer_where['question_id'] = $row->question_id;
			$answer = $this->answer_model->get_answer($answer_where);
			$row->answer = $answer->result();
			$answer->free_result();
		}
		
		/*
		$query = $this->question_model->get_question_with_answer($where);
		$question_rows = $query->result();
		*/
		$count = $query->num_rows();
		
		
		
		echo '<br/>';
		echo json_encode(array('status'=>$status,'msg' => $msg,'count'=>$count,'result' => $question_rows));
		echo date("Y-m-d H:i:s").'<br/>';
	}
	public function insert_question()
	{
		$status = '';
		$msg = '';
		/*
		$user_id = $this->input->post('user_id',true);
		$question_latitude = $this->input->post('question_latitude',true);
		$question_longitude = $this->input->post('question_longitude',true);
		$question_time = date("Y-m-d H:i:s");
		$question_content = $this->input->post('question_content',true);
		$question_distance_limited = $this->input->post('question_distance_limited',true);
		$question_is_photo_needed = $this->input->post('question_is_photo_needed',true);
		$question_time_left = $this->input->post('question_time_left',true);
		$question_reward = $this->input->post('question_reward',true);
		
		$is_pay = $this->input->post('is_pay',true);
		$limit_min = $this->input->post('limit_min',true);*/
		
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
					echo 'count  = '.$query->num_rows() .'<br/>';
					if($query->num_rows()>0)
					{
						foreach($query->result() as $row)
						{	
							$distance = $this->geolocation->get_distance($question_latitude,$question_longitude,$row->location_latitude,$row->location_longitude);
							echo 'distance = '.$distance.'<br/>';
							if($distance < $question_distance_limited)
							{
								echo 'user_id = '.$row->user_id;
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
		//while(TRUE){
		/*
		$user_id = $this->input->post('user_id',true);
		$question_id = $this->input->post('question_id',true);
		$answer_latitude = $this->input->post('answer_latitude',true);
		$answer_longitude = $this->input->post('answer_longitude',true);
		$answer_time = date("Y-m-d H:i:s");
		$answer_photo_url = $this->input->post('answer_photo_url',true);
		$answer_content	= $this->input->post('answer_content',true);
		$is_best_answer = $this->input->post('is_best_answer',true);
		$answer_score = 0;//$this->input->post('answer_score',true);
		*/
		$user_id = rand(1,3);
		$question_id = rand(1,1000);
		$answer_latitude = rand (-90,90).'.'.rand (1000,9999);
		$answer_longitude = rand (-180,180).'.'.rand (1000,9999);;
		$answer_time = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) - (60  * rand (1,30)));
		$answer_photo_url = '';
		$answer_content	= iconv('UTF-8', 'BIG5//TRANSLIT//IGNORE',$this->getRandomString(rand (1,50)));
		$is_best_answer = 0;
		$answer_score = 0;//$this->input->post('answer_score',true);
		if(!empty($answer_content))
		{
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
			);
			
			if($this->answer_model->insert_answer($data))
			{
				$status = 'ok';
				$msg = 'Question insert sucessfully.';
			}
			else
			{
				$status = 'fail';
				$msg = 'Answer insert Databse error.';
			}
		}
		else
		{
			$status = 'fail';
			$msg = 'miss post value';
		}
		//}
		
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