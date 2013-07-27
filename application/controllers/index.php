<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends CI_Controller {
	
	public function __construct()
	{
		parent::__construct();
        $this->load->library('image_manipulation');

	}
	
	public function index()
	{
		$sess = $this->session->all_userdata();
		
		//echo $this->session->userdata('howeather');
		$this->load->view('test_upload_view');
	}
	
	public function upload()
	{
        
		$this->load->library('S3');

		if(isset($_POST['Submit'])){
			$fileName = $_FILES['theFile']['name'];
			$fileTempName = $_FILES['theFile']['tmp_name'];
			
            $destination = FCPATH.'upload/'.$fileName;
            $this->image_manipulation->create_thumbs($fileTempName, $destination);
            
            $path_parts = pathinfo($fileName);
            $thumbName = $path_parts['filename'].'_thumb.'.$path_parts['extension'];
            
			//create a new bucket
			$this->s3->putBucket("yunnnyunnn_test", S3::ACL_PUBLIC_READ);
			//move the file
			if ($this->s3->putObjectFile($fileTempName, "yunnnyunnn_test", $fileName, S3::ACL_PUBLIC_READ)) {
				echo "We successfully uploaded your file.";
                
                //move the file
                if ($this->s3->putObjectFile($destination, "yunnnyunnn_test", $thumbName, S3::ACL_PUBLIC_READ)) {
                    echo "We successfully uploaded your thumb.";
                    // 刪除upload裡的暫存檔案
                    unlink($destination);
                }else{
                    echo "Something went wrong while uploading your thumb... sorry.";
                }
                
                
			}else{
				echo "Something went wrong while uploading your file... sorry.";
			}
             
		}
			
	}
}