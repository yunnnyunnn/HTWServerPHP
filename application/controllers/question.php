<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Question extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('question_model');
		$this->load->model('location_log_model');
	}
	public function index()
	{
		$value = $this->uri->segment(3);
		$this->load->library('geolocation');
		$distance = $this->geolocation->get_distance(32.9697, -96.80322, 29.46786, -98.53506);
		echo json_encode(array('Hello'=>date("Y-m-d H:i:s"),'price' => QUESTION_PUSH_PRICE , 'distance' => $distance));
	}
	public function get_question()
	{
		$where = array();
		$query = $this->question_model->get_question($where);
		
		$hr = 5;
		$current_date = strtotime(date("Y-m-d H:i:s"));
		$limit_date = $current_date-(60*60*$hr);
		$time = date("Y-m-d H:i:s", $limit_date);
		$query1 = $this->location_log_model->get_group_by_location_log($time,'user_id');
		$question_time = $query->row()->question_time;
		echo json_encode(array('Hello'=>'World','question_time'=>$question_time,'result' => $query1->result()));
	}
	public function insert_question()
	{
		$status = '';
		$msg = '';
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
				if($is_pay)
				{
					$this->load->library('geolocation');
					
					$distance = $this->geolocation->get_distance(32.9697, -96.80322, 29.46786, -98.53506);
				}
				else
				{
					
					
				}		
				$status = 'ok';
				$msg = 'Question insert sucessfully.';
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
	
	
}