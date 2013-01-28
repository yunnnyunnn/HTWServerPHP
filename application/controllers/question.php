<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Question extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('question_model');
	}
	public function index()
	{
		echo json_encode(array('Hello'=>'World'));
	}
	public function get_weather()
	{
		$where = array();
		$query = $this->question_model->get_question($where);
		$question_time = $query->row()->question_time;
		echo json_encode(array('Hello'=>'World','question_time'=>$question_time,'result' => $query->result()));
	}
	public function insert_weather()
	{
		$data = array('user_id'=>'2');
		$result = $this->question_model->insert_question($data);
		
		echo json_encode(array('result'=>$result));
	}
}