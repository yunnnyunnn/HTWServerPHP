<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
圖片操作功能
create_thumb 新增壓縮檔
**/
class Image_manipulation{
	
	
	public function __construct()
    {
		
    }
	
	
	public function create_thumbs($source,$thumb_path,$width=200)
	{
		$CI =& get_instance();		
		//$CI->load->library("image_lib");
        
        
        
        $config['image_library'] = 'ImageMagick';
        $config['source_image']	= $source;//FCPATH.'upload/123.jpg';
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = TRUE;
        $config['width']	 = $width;
        $config['height']	= $width;
        $config['thumb_marker']	= '';
        $config['new_image'] = $thumb_path;

        
        $CI->load->library('image_lib');
        $CI->image_lib->initialize($config);
        if ( ! $CI->image_lib->resize())
        {
            echo $CI->image_lib->display_errors();
        }
        
        
		/*
		$config['image_library'] = 'gd2';
		$config['source_image']	= $source;
		$config['create_thumb'] = TRUE;
		$config['maintain_ratio'] = TRUE;
		$config['width'] = $width;
		$config['height'] = $width;
		$config['new_image'] =$thumb_path;


        $CI->load->library('image_lib', $config);
        
        if ( ! $CI->image_lib->resize())
        {
            echo $CI->image_lib->display_errors();
        }
		$CI->image_lib->clear();
		
		*/
  
        
    
       
		
		 
	}
	


}






?>