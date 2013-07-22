<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends My_Controller {

	public function __construct()
	{
		parent::__construct();
		

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
    
    public function testit()
    {
        $response = $this->curl->simple_post('http://yunnnyunnn.com/transfer.php', array('userID'=>'31769'), array(CURLOPT_BUFFERSIZE => 10));
        $responseArray = json_decode($response, true);
        
        $money = $responseArray['money'];
        echo "money:$money";
        
        $userID = $responseArray['userID'];
        echo "userID:$userID";
        
        $shares = $responseArray['shares'];
        
        foreach($shares as $share){
            $x = $share['x'];
            echo "x:$x";
            $y = $share['y'];
            echo "y:$y";
            $weather = $share['weather'];
            echo "weather:$weather";
            $pic = $share['pic'];
            echo "pic:$pic";
            $msg = $share['msg'];
            echo "msg:$msg";
            $time = $share['time'];
            echo "time:$time";
        }


        
        //var_dump($responseArray);
    }

	
	
}