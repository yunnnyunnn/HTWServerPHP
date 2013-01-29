<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();	
	}
	public function index()
	{
		echo 'user login';
	}
	public function login()
	{
		$sess = array(
			'user_name'=>'ding' , 
			'phone' => '0983781731' ,
			'token' => $token = md5(uniqid(rand(), TRUE))
		);
		$this->session->set_userdata('user',$sess);
	}
}