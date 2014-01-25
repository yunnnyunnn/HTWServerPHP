<?php

class Test_model extends My_Model
{
	function __construct()
	{
		parent::__construct();

	}
	
	function get_time()
	{
		return $this->db->query("SELECT @@global.time_zone, @@session.time_zone");
	}
	
	
}