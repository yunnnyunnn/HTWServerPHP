<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class My_Controller extends CI_Controller {
	
	var $user_id = NULL;
	var $user_email = NULL;
	
	public function __construct()
	{
		parent::__construct();	
		if(isset($_GET['howeatoken']))
		{
			
		}
		else
		{
			$is_login = $this->session->userdata('user');
			if(!$is_login||empty($is_login['token']))
			{
				redirect('user');
			}
			else
			{
				$this->user_id = $is_login['user_id'];
				$this->user_email = $is_login['user_email'];
			}
		}
	}
}