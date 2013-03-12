<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();	
	}
	
	public function index()
	{
		$this->load->view('test_upload_view');
	}
	
	public function upload()
	{
		$this->load->library('S3');

		if(isset($_POST['Submit'])){
			$fileName = 'userID/'.$_FILES['theFile']['name'];
			$fileTempName = $_FILES['theFile']['tmp_name'];
			
			//create a new bucket
			$this->s3->putBucket("yunnnyunnn_test", S3::ACL_PUBLIC_READ);
			//move the file
			if ($this->s3->putObjectFile($fileTempName, "weather_bucket", $fileName, S3::ACL_PUBLIC_READ)) {
				echo "We successfully uploaded your file.";
			}else{
				echo "Something went wrong while uploading your file... sorry.";
			}
		}
			
	}
}