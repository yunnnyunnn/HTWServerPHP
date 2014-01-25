<?php

class My_Model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
        $this->db->query("SET time_zone='+8:00'");
        //echo $this->db->query("SET time_zone='+8:00'");

	}
	
}