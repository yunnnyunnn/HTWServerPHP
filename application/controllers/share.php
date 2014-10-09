<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    
    class Share extends My_Controller {
        
        
        public function __construct()
        {
            parent::__construct();
            $this->load->model('user_model');
            $this->load->model('share_model');
            $this->load->model('share_comment_model');
            $this->load->model('share_likes_model');
            $this->load->model('notification_model');
            $this->load->model('push_queue_ios_model');
			$this->load->model('push_queue_android_model');
            $this->load->model('device_model');
            $this->load->library('S3');
            $this->load->library('payload_maker');
            $this->load->library('wp_push_notification_maker');
			$this->load->library('android_push_notification_maker');
            $this->load->library('image_manipulation');


        }
        public function index()
        {
            echo json_encode(array('Hello'=>'Weather'));
        }
        
/////////////////////////////////////////////////////
        
////////////////以下為對share本身的操作/////////////////
        
        public function get_share_preview_on_map()
        {
            // get the request sender's user_id
            $user_id = $this->user_id;
            $where = array();
            
            // prevent from not sending post value
            if(!isset($_POST["screen_icon_width_ratio"]) OR !isset($_POST["screen_icon_height_ratio"]) OR !isset($_POST["latDelta"]) OR !isset($_POST["longDelta"]))
            {
                echo json_encode(array('msg' => 'post value not set',
                                       'status' => 'fail'));
                return;
            }
            $screen_icon_width_ratio = $this->input->post('screen_icon_width_ratio', TRUE);
            $screen_icon_height_ratio = $this->input->post('screen_icon_height_ratio', TRUE);
            $latDelta = $this->input->post('latDelta', TRUE);
            $longDelta = $this->input->post('longDelta', TRUE);
            
            // calculate the minimum distance which should be exist between each two shares
            $latDelta_minimum_distance = $latDelta/$screen_icon_width_ratio;
            $longDelta_minimum_distance = $longDelta/$screen_icon_height_ratio;
            
            // if there's a time limit
            $share_time = $this->input->post('share_time', TRUE);
            
            if($share_time)
            {
                
                $where['share_time >='] = date('Y-m-d H:i:s', strtotime($share_time));
            }
            
            // if you only want to get sepcific user's shares
            $get_share_user_id = $this->input->post('user_id', TRUE);
            if (isset($_POST["user_id"]))
            {
                $where['user.user_id'] = $get_share_user_id;
            }
            
            // if there's area limit
            $share_latitude_max = $this->input->post('share_latitude_max', TRUE);
            $share_latitude_min = $this->input->post('share_latitude_min', TRUE);
            $share_longitude_max = $this->input->post('share_longitude_max', TRUE);
            $share_longitude_min = $this->input->post('share_longitude_min', TRUE);
            if(isset($_POST["share_latitude_max"]) && isset($_POST["share_latitude_min"]) && isset($_POST["share_longitude_max"]) && isset($_POST["share_longitude_min"]))
            {
                
                $where['share_latitude <='] = $share_latitude_max;
                $where['share_latitude >='] = $share_latitude_min;
                $where['share_longitude <='] = $share_longitude_max;
                $where['share_longitude >='] = $share_longitude_min;
            }
            
            $field = array('share.*', 'timediff(share_time, now()) as share_timediff', 'user.user_nickname','user.user_medal','user.user_id');
            $query = $this->share_model->get_share($where, $field);
            
            $shares = $query->result();
            
            $share_preview = array();
            
            foreach($shares as $share)
            {
                
                $found = FALSE;
                foreach($share_preview as $stored_share)
                {
                    $latDelta_between = abs($share->share_latitude - $stored_share->share_latitude);
                    $longDelta_between = abs($share->share_longitude - $stored_share->share_longitude);
                    
                    if ($latDelta_between < $latDelta_minimum_distance && $longDelta_between < $longDelta_minimum_distance)
                    {
                        $found = TRUE;
                        $stored_share->child_shares[] = $share->share_id;
                        break;
                    }
                    
                }
                
                if(!$found){
                    $share->child_shares = array();
                    $share_preview[] = $share;
                }
            }
            
            
            // send the final
            echo json_encode(array('constraints' => $where,
                                   'result' => $share_preview,
                                   'msg' => 'get share preview on map ok',
                                   'status' => 'success'
                                   ));
            
        }
        
        public function get_shares_by_share_id()
        {
            $user_id = $this->user_id;
            $where = array();
            
            // get a list of share_id which we will use to get shares
            if(!isset($_POST["share_ids"]))
            {
                echo json_encode(array('msg' => 'post value not set',
                                       'status' => 'fail'));
                return;
            }
            
            $share_ids_json = $this->input->post('share_ids', TRUE);
            $share_ids = json_decode($share_ids_json, TRUE);
            
            
            $field = array('share.*', 'timediff(share_time, now()) as share_timediff', 'user.user_nickname','user.user_medal','user.user_id');
            $query = $this->share_model->get_share_where_in('share.share_id', $share_ids, $field);
            
            $shares = $query->result();
            
            // start attaching comments and likes
            
            $sc_field = array('share_comment.*', 'timediff(share_comment_time, now()) as share_comment_timediff', 'user.user_nickname','user.user_medal','user.user_id');
            $sl_field = array('share_likes.*', 'user.user_nickname','user.user_medal','user.user_id');
            
            foreach($shares as $share)
            {
                $share_id = $share->share_id;
                $where_sub = array('share_id'=>$share_id);
                
                $query_comment = $this->share_comment_model->get_share_comment($where_sub, $sc_field);
                $share->share_comment = $query_comment->result();
                $share->share_comment_count = $this->share_comment_model->get_share_comment_count($where_sub);
                
                $query_like = $this->share_likes_model->get_share_likes($where_sub,$sl_field);
                $share->share_likes = $query_like->result();
                $share->share_likes_count = $this->share_likes_model->get_share_likes_count($where_sub);
                
                $where_sub['user_id'] = $user_id;
                $share->is_user_like_share = $this->share_likes_model->get_share_likes_count($where_sub);
            }
            
            // 將最後結果送出
            echo json_encode(array('constraints' => $share_ids,
                                   'result' => $shares,
                                   'msg' => 'get share ok',
                                   'status' => 'success'
                                   ));
        }
        
        public function get_share()
        {
            
            // 如果什麼都沒有傳，就全部抓
             $user_id = $this->user_id;
            $where = array();
            
            // 如果有傳時間限制
            $share_time = $this->input->post('share_time', TRUE);

            if($share_time)
            {
                
                $where['share_time >='] = date('Y-m-d H:i:s', strtotime($share_time));
            }
            
            // 如果有傳區域限制
            $share_latitude_max = $this->input->post('share_latitude_max', TRUE);
            $share_latitude_min = $this->input->post('share_latitude_min', TRUE);
            $share_longitude_max = $this->input->post('share_longitude_max', TRUE);
            $share_longitude_min = $this->input->post('share_longitude_min', TRUE);
            if(isset($_POST["share_latitude_max"]) && isset($_POST["share_latitude_min"]) && isset($_POST["share_longitude_max"]) && isset($_POST["share_longitude_min"]))
            {
                
                $where['share_latitude <='] = $share_latitude_max;
                $where['share_latitude >='] = $share_latitude_min;
                $where['share_longitude <='] = $share_longitude_max;
                $where['share_longitude >='] = $share_longitude_min;
            }
            
            // 如果有指定作者
            $get_share_user_id = $this->input->post('user_id', TRUE);
            if (isset($_POST["user_id"]))
            {
                $where['user.user_id'] = $get_share_user_id;
            }

            
            $share_id_max = $this->input->post('share_id_max', TRUE);
            if(isset($_POST["share_id_max"]))
            {
                
                $where['share_id <='] = $share_id_max;
            }
            
            //$field = 'user_id, share_id, share_content, share_weather_type, share_photo_url, share_latitude, share_longitude, timediff(share_time, now()) as share_time, (select user_nickname from user where user_id = share.user_id) as user_nickname';
            $field = array('share.*', 'timediff(share_time, now()) as share_timediff', 'user.user_nickname','user.user_medal','user.user_id');
            
            $query = $this->share_model->get_share($where, $field);
            
            $shares = $query->result();
            
            // 這邊開始將每一篇的comment抓下來
            $sc_field = array('share_comment.*', 'timediff(share_comment_time, now()) as share_comment_timediff', 'user.user_nickname','user.user_medal','user.user_id');
            $sl_field = array('share_likes.*', 'user.user_nickname','user.user_medal','user.user_id');

            foreach($shares as $share)
            {
                $share_id = $share->share_id;
                $where_sub = array('share_id'=>$share_id);
                         
                $query_comment = $this->share_comment_model->get_share_comment($where_sub, $sc_field);
                $share->share_comment = $query_comment->result();
                $share->share_comment_count = $this->share_comment_model->get_share_comment_count($where_sub);
                
                $query_like = $this->share_likes_model->get_share_likes($where_sub,$sl_field);
                $share->share_likes = $query_like->result();
                $share->share_likes_count = $this->share_likes_model->get_share_likes_count($where_sub);
                
                $where_sub['user_id'] = $user_id;
                $share->is_user_like_share = $this->share_likes_model->get_share_likes_count($where_sub);
            }
            
            // 將最後結果送出
            echo json_encode(array('constraints' => $where,
                                   'result' => $shares,
                                   'msg' => 'get share ok',
                                   'status' => 'success'
                                   ));
        }
        
        public function get_share_stream() {
            // 如果什麼都沒有傳，就全部抓
             $user_id = $this->user_id;
            $where = array();
            
            // 指定這次抓的串流從哪一篇開始
            $share_id_max = $this->input->post('share_id_max', TRUE);
            if(isset($_POST["share_id_max"]))
            {
                
                $where['share_id <'] = $share_id_max;
            }
            
            
            // 指定一次抓幾篇，沒有指定的話預設值是25篇
            $share_count = 25;
            if (isset($_POST["share_count"])) {
                
                $share_count = $this->input->post('share_count', TRUE);

            }
            
            // 如果有指定作者
            $get_share_user_id = $this->input->post('user_id', TRUE);
            if (isset($_POST["user_id"]))
            {
                $where['user.user_id'] = $get_share_user_id;
            }
            
            
            // if there's area limit
            $share_latitude_max = $this->input->post('share_latitude_max', TRUE);
            $share_latitude_min = $this->input->post('share_latitude_min', TRUE);
            $share_longitude_max = $this->input->post('share_longitude_max', TRUE);
            $share_longitude_min = $this->input->post('share_longitude_min', TRUE);
            if(isset($_POST["share_latitude_max"]) && isset($_POST["share_latitude_min"]) && isset($_POST["share_longitude_max"]) && isset($_POST["share_longitude_min"]))
            {
                
                $where['share_latitude <='] = $share_latitude_max;
                $where['share_latitude >='] = $share_latitude_min;
                $where['share_longitude <='] = $share_longitude_max;
                $where['share_longitude >='] = $share_longitude_min;
            }
            
            
            //$field = 'user_id, share_id, share_content, share_weather_type, share_photo_url, share_latitude, share_longitude, timediff(share_time, now()) as share_time, (select user_nickname from user where user_id = share.user_id) as user_nickname';
            $field = array('share.*', 'timediff(share_time, now()) as share_timediff', 'user.user_nickname','user.user_medal','user.user_id');
            $query = $this->share_model->get_share($where, $field, $share_count);
            
            $shares = $query->result();
            
            // 這邊開始將每一篇的comment抓下來
            $sc_field = array('share_comment.*', 'timediff(share_comment_time, now()) as share_comment_timediff', 'user.user_nickname','user.user_medal','user.user_id');
            $sl_field = array('share_likes.*', 'user.user_nickname','user.user_medal','user.user_id');

            foreach($shares as $share)
            {
                $share_id = $share->share_id;
                $where_sub = array('share_id'=>$share_id);

                $query_comment = $this->share_comment_model->get_share_comment($where_sub, $sc_field);
                $share->share_comment = $query_comment->result();
                $share->share_comment_count = $this->share_comment_model->get_share_comment_count($where_sub);
                
                $query_like = $this->share_likes_model->get_share_likes($where_sub,$sl_field);
                $share->share_likes = $query_like->result();
                $share->share_likes_count = $this->share_likes_model->get_share_likes_count($where_sub);
                
                 $where_sub['user_id'] = $user_id;
                $share->is_user_like_share = $this->share_likes_model->get_share_likes_count($where_sub);
            }
            
            // 將最後結果送出
            echo json_encode(array('constraints' => $where,
                                   'result' => $shares,
                                   'msg' => 'get share ok',
                                   'status' => 'success'
                                   ));
        }
        
        
        
        ###this method should be combined with get_shares_by_share_id###
        public function get_one_share() {
             $user_id = $this->user_id;
            $where = array();
            
            // 指定這次抓的串流從哪一篇開始
            $share_id_max = $this->input->post('share_id_max', TRUE);
            if(isset($_POST["share_id_max"]))
            {
                
                $where['share_id'] = $share_id_max;
            }
                
            $share_count = 1;
            
            
            $field = array('share.*', 'timediff(share_time, now()) as share_timediff', 'user.user_nickname','user.user_medal','user.user_id');
            $query = $this->share_model->get_share($where, $field, $share_count);
            
            $shares = $query->result();
            
            // 這邊開始將每一篇的comment抓下來
               
            $sc_field = array('share_comment.*', 'timediff(share_comment_time, now()) as share_comment_timediff', 'user.user_nickname','user.user_medal','user.user_id');
            $sl_field = array('share_likes.*', 'user.user_nickname','user.user_medal','user.user_id');
  
            foreach($shares as $share)
            {
                $share_id = $share->share_id;
                $where_sub = array('share_id'=>$share_id);
             
                $query_comment = $this->share_comment_model->get_share_comment($where_sub, $sc_field);
                $share->share_comment = $query_comment->result();
                $share->share_comment_count = $this->share_comment_model->get_share_comment_count($where_sub);
                
                $query_like = $this->share_likes_model->get_share_likes($where_sub,$sl_field);
                $share->share_likes = $query_like->result();
                $share->share_likes_count = $this->share_likes_model->get_share_likes_count($where_sub);
                
                  $where_sub['user_id'] = $user_id;
                $share->is_user_like_share = $this->share_likes_model->get_share_likes_count($where_sub);
            }
            
            // 將最後結果送出
            echo json_encode(array('constraints' => $where,
                                   'result' => $shares,
                                   'msg' => 'get share ok',
                                   'status' => 'success'
                                   ));
        }
        
		public function get_specific_shares() 
		{
             $user_id = $this->user_id;
		    $sid_array = array();
		    $share_id_json = $this->input->post('share_id_json', TRUE);
		    if(isset($_POST["share_id_json"]))
		    {
               
		 	   $sid_array = json_decode($share_id_json,TRUE);

		    }
		    $shares = array();
            $field = array('share.*', 'timediff(share_time, now()) as share_timediff', 'user.user_nickname');	
            $sc_field = array('share_comment.*', 'timediff(share_comment_time, now()) as share_comment_timediff', 'user.user_nickname');
            $sl_field = array('share_likes.*', 'user.user_nickname','user.user_medal','user.user_id');
		   foreach($sid_array as $specific)
		   {
			   $where = array();
			   $share_count = 1;
			   $where['share_id'] = $specific['share_id'];
	 	
			   $query = $this->share_model->get_share($where, $field, $share_count);	
			 
			   if($query->num_rows()>0)
			   {
			 	  $one_share = $query->row();
			 	  $share_id = $one_share->share_id;
				  $where_sub = array('share_id'=>$share_id);
				 
				  $query_comment = $this->share_comment_model->get_share_comment($where_sub, $sc_field);
				  $one_share->share_comment = $query_comment->result();
	              $one_share->share_comment_count = $this->share_comment_model->get_share_comment_count($where_sub);
				  
                  $query_like = $this->share_likes_model->get_share_likes($where_sub,$sl_field);			
				  $one_share->share_likes = $query_like->result();
				  $one_share->share_likes_count = $this->share_likes_model->get_share_likes_count($where_sub);
				  
                     $where_sub['user_id'] = $user_id;
                $one_share->is_user_like_share = $this->share_likes_model->get_share_likes_count($where_sub);
                   
                   $shares[] = $one_share;
			   }  
		   }
		  // 將最後結果送出
		   echo json_encode(array('result' => $shares,
								 'msg' => 'get share ok',
								 'status' => 'success',
                               
								 ));
	  }
		
        /* 已經跟get_share()合併
        public function get_user_share()
        {
            // 如果什麼都沒有傳，就全部抓
            
            $where = array();
            
            $user_id = $this->user_id;
            
            $where['user_id'] = $user_id;
            
            $query = $this->share_model->get_share($where);
            
            $shares = $query->result();
            
            // 這邊開始將每一篇的comment抓下來
            foreach($shares as $share)
            {
                $share_id = $share->share_id;
                $where_comment = array('share_id'=>$share_id);
                $query_comment = $this->share_comment_model->get_share_comment($where_comment);
                $share->share_comment = $query_comment->result();
            }
            
            // 將最後結果送出
            echo json_encode(array('constraints' => $where,
                                   'result' => $shares
                                   ));
        }
         */
        
        public function insert_share()
        {
         
            $user_id = $this->user_id;

            $share_content = $this->input->post('share_content', TRUE);
            $share_weather_type = $this->input->post('share_weather_type', TRUE);
            $share_latitude = $this->input->post('share_latitude', TRUE);
            $share_longitude = $this->input->post('share_longitude', TRUE);
            
            // 防止沒有傳post value
            if(!isset($_POST["share_content"]) OR !isset($_POST["share_weather_type"]) OR !isset($_POST["share_latitude"]) OR !isset($_POST["share_longitude"]))
            {
                echo json_encode(array('msg' => 'insert share post value not set',
                                       'status' => 'fail'));
                return;
            }
            
            
            $fileName = '';
            if(isset($_POST['Submit'])){
                
                $fileName = "$user_id/".time().".jpg";
                $fileTempName = $_FILES['theFile']['tmp_name'];
                
                $thumbTempName = "$user_id".time().".jpg";
                $destination = FCPATH.'upload/'.$thumbTempName;
                $this->image_manipulation->create_thumbs($fileTempName, $destination);
                
                
                $path_parts = pathinfo($fileName);
                $thumbName = $path_parts['dirname'].'/'.$path_parts['filename'].'_thumb.'.$path_parts['extension'];
                
                //create a new bucket
                //$this->s3->putBucket("weather_bucket", S3::ACL_PUBLIC_READ);
                //move the file
                if ($this->s3->putObjectFile($fileTempName, "weather_bucket", $fileName, S3::ACL_PUBLIC_READ)) {
                    //echo "We successfully uploaded your file.";
                    
                    //move the file
                    if ($this->s3->putObjectFile($destination, "weather_bucket", $thumbName, S3::ACL_PUBLIC_READ)) {
                        //echo "We successfully uploaded your thumb.";
                        // 刪除upload裡的暫存檔案
                        unlink($destination);
                    }else{
                        //echo "Something went wrong while uploading your thumb... sorry.";
                        echo json_encode(array('msg' => 'photo not uploaded',
                                               'status' => 'fail'));
                        return;
                    }
                    
                    
                }else{
                    //echo "Something went wrong while uploading your file... sorry.";
                    echo json_encode(array('msg' => 'photo not uploaded',
                                           'status' => 'fail'));
                    return;
                }
            }
            
            $data = array(
                          'user_id'=>$user_id,
                          'share_content'=>$share_content,
                          'share_weather_type'=>$share_weather_type,
                          'share_photo_url'=>$fileName,
                          'share_latitude'=>$share_latitude,
                          'share_longitude'=>$share_longitude,
                          'share_time'=>date("Y-m-d H:i:s"),
                          'share_likes'=>0
                          );
            
            $result = $this->share_model->insert_share($data);

            //////////////增加使用者經驗值
            if(isset($_POST['Submit']))
            {
                 $new_exp = $this->update_user_exp($user_id,$this->insert_share_with_photo);
                // 這邊開始檢視需不需要給他新的medal
                $this->check_and_insert_user_medal($user_id, $new_exp);

            }
            else
            {
                 $new_exp = $this->update_user_exp($user_id,$this->insert_share);
                // 這邊開始檢視需不需要給他新的medal
                $this->check_and_insert_user_medal($user_id, $new_exp);

            }


            
            echo json_encode(array('msg' => 'insert share ok',
                                   'status' => 'success','share_photo_url'=>$fileName));
        }
        
        public function delete_share()
        {
            // 刪除時必須提供user_id和share_id
            
            $user_id = $this->user_id;
            $share_id = $this->input->post('share_id', TRUE);
            
            // 防止沒有傳post value
            if(!isset($_POST["share_id"]))
            {
               echo json_encode(array('msg' => 'delete share post value not set',
                                      'status' => 'fail'));
                return;
            }
            
            
            
            $data = array(
                          'user_id'=>$user_id,
                          'share_id'=>$share_id
                          );
            
            $result = $this->share_model->delete_share($data);
            
            $where = array(
            
            'notification_type <'=>2,
            'post_id'=>$share_id
                           
            
            );
            
            $this->notification_model->delete_notification($where);
            
               echo json_encode(array('msg' => 'delete share ok',
                                      'status' => 'success'));

        }
        
//////////////////////////////////////////////////////////
        
////////////////以下為對share_comment的操作/////////////////

        
        public function insert_share_comment()
        {
            $user_id = $this->user_id;
            $share_id = $this->input->post('share_id', TRUE);
            $share_comment_content = $this->input->post('share_comment_content', TRUE);
            
            $where = array (
                            'user_id'=> $user_id
                            );
            
            $query_user = $this->user_model->get_user('*', $where);
            $user_nickname = $query_user->row()->user_nickname;
            
            
            
            // 防止沒有傳post value
            if(!isset($_POST["share_id"]) OR !isset($_POST["share_comment_content"]))
            {
                echo json_encode(array('msg' => 'insert share comment post value not set',
                                        'status' => 'fail'));
                return;
            }
            
            
            $share_comment_time = date("Y-m-d H:i:s");
            $data = array(
                          'user_id'=>$user_id,
                          'share_id'=>$share_id,
                          'share_comment_content'=>$share_comment_content,
                          'share_comment_time'=> $share_comment_time,
                          );
            
            $result = $this->share_comment_model->insert_share_comment($data);
            
            
            // 開始制作一個通知
            // 先抓到要傳給哪些人
            
            $where = array(
                           'share_id' => $share_id
                           );
            $field = array('share.user_id');
            
            $receiver_array = array();
            
            // 抓到作者
            $query = $this->share_model->get_share($where, $field);
            $query_result = $query->result();
            if ($query->num_rows() > 0) {
                foreach ($query_result as $single_share) {
                    if (!in_array($single_share->user_id, $receiver_array)&&($single_share->user_id!=$user_id))
                    {
                        $receiver_array[] = $single_share->user_id;
                    }
                }
            }
            
            // 抓到推文者
            $query_comment = $this->share_comment_model->get_share_comment($where);
            $query_comment_result = $query_comment->result();
            if ($query_comment->num_rows() > 0) {
                foreach ($query_comment_result as $single_comment) {
                    if (!in_array($single_comment->user_id, $receiver_array)&&($single_comment->user_id!=$user_id))
                    {
                        $receiver_array[] = $single_comment->user_id;
                    }
                    
                }
            }
            
            
            // 開始制作通知
			$notification_id = '';
            foreach ($receiver_array as $receiver) {
                $data = array (
                'user_id_sender' => $user_id,
                'user_id_receiver' => $receiver,
                'notification_type' => 0,
                'post_id' => $share_id,
                'notification_time' => date("Y-m-d H:i:s"),
                'notification_is_record' => 0,
                );
                
                $notification_id = $this->notification_model->insert_notification($data);

            }
            /////////////////////////////////
            // 取得所有的device token
            $device_token_array = $this->get_device_token($receiver_array);
            
            
            
            
            // 開始製作推播db
            foreach ($device_token_array as $device_token) {
                if ($device_token['device_type'] == 1) { // iOS推播
                    $loc_args = array($user_nickname, $share_comment_content);
                    $payload = $this->payload_maker->make_payload('PUSH_MESSAGE_TYPE_0', $loc_args, $share_id);
                    if (strlen($payload) <= 256)
                    {
                        $data  = array(
                                       'pqo_device_token' => $device_token['device_token'],
                                       'pqi_payload' => $payload,
                                       'pqi_time_queued' => date("Y-m-d H:i:s")
                                       );
                        $result = $this->push_queue_ios_model->insert_push_queue_iOS($data);
                    }
                    
                }
                else if ($device_token['device_type'] == 3) { // windows phone推播
                                   
                    $result= $this->wp_push_notification_maker->send_toast('Howeather' ,$user_nickname.'left comment on share:'.$share_comment_content,'/all_share_page.xaml?type=detail&amp;share_id='.$share_id.'' ,2,$device_token['device_token']);
                    //$result= $this->wp_push_notification_maker->send_toast('toast','message','',$device_token['device_token']);
                   
                }
                else if ($device_token['device_type'] == 2) { // android推播
                    $payload = $this->android_push_notification_maker->make_payload('0', $share_id, $user_nickname,$notification_id,$share_comment_content);
					$data  = array(
								   'registration_id' => $device_token['device_token'],
								   'pqa_payload' => $payload,
								   'pqa_time_queued' => date("Y-m-d H:i:s")
								   );
					$result = $this->push_queue_android_model->insert_push_queue_android($data);
                }
                
            }
            
            
            echo json_encode(array('msg' => 'comment succesfully saved',
                                    'status' => 'success','share_comment_time' =>  $share_comment_time));
        }
        

        
////////////////////////////////////////////////////////

////////////////以下為對share_likes的操作/////////////////

        public function insert_share_likes()
        {
            $user_id = $this->user_id;
            $share_id = $this->input->post('share_id', TRUE);
              

         
            
           /// 防止沒有傳post value
            if(!isset($_POST["share_id"]))
            {
               echo json_encode(array('msg' => 'insert share likes post value not set',
                                      'status' => 'fail'));
                return;
            }
            
            
            
            $data = array(
                          'user.user_id'=>$user_id,
                          'share_id'=>$share_id,
                          );
            
            // 先檢查是否已經加過喜歡了，如果有的話就不給加
            $duplicateChecker = $this->share_likes_model->get_share_likes($data);
            if($duplicateChecker->num_rows()>0)
            {
               echo json_encode(array('msg' => 'insert share likes more than once',
                                      'status' => 'fail'));
                return;
            }
            
            $data = array(
                          'user_id'=>$user_id,
                          'share_id'=>$share_id,
                          );
            
            $result = $this->share_likes_model->insert_share_likes($data);

            //為了判斷這個share的user是不是insert的user
            $getshare=$this->share_model->get_share(array('share_id' => $share_id));
            $share_liked_user_id = $getshare->row()->user_id;
            if($share_liked_user_id!=$user_id)
            {
                // 為被按讚的人增加經驗值
                $new_exp = $this->update_user_exp($share_liked_user_id,$this->share_liked);
                
                // 這邊開始檢視需不需要給他新的medal
                $this->check_and_insert_user_medal($share_liked_user_id, $new_exp);
            }
            
            // 開始制作一個通知
            // 先抓到要傳給哪些人
            
            
            /********** duplicated script
            $where = array(
                           'share_id' => $share_id
                           );
            $field = array('share.user_id');
            
            
            // 抓到作者
            $query = $this->share_model->get_share($where, $field);
            $query_result = $query->result();
            if ($query->num_rows() > 0) {
                foreach ($query_result as $single_share) {
                    if (!in_array($single_share->user_id, $receiver_array)&&($single_share->user_id!=$user_id))
                    {
                        $receiver_array[] = $single_share->user_id;
                    }
                }
            }
            */
            
            $receiver_array = array();
            if($share_liked_user_id!=$user_id)
            {
                $receiver_array[] = $share_liked_user_id;
            }
            
            // 開始制作通知
			$notification_id = '';
            foreach ($receiver_array as $receiver) {
                $data = array (
                               'user_id_sender' => $user_id,
                               'user_id_receiver' => $receiver,
                               'notification_type' => 1,
                               'post_id' => $share_id,
                               'notification_time' => date("Y-m-d H:i:s"),
                               'notification_is_record' => 0,
                               );
                
                $notification_id = $this->notification_model->insert_notification($data);
                
            }
            
            
            /////////////////////////////////
            
            // 取得所有的device token
            $device_token_array = $this->get_device_token($receiver_array);
            
            
            $where = array (
                            'user_id'=> $user_id
                            );
            
            $query_user = $this->user_model->get_user('*', $where);
            $user_nickname = $query_user->row()->user_nickname;
            // 開始製作推播db
            foreach ($device_token_array as $device_token) {
                if ($device_token['device_type'] == 1) { // iOS推播
                    $loc_args = array($user_nickname);
                    $payload = $this->payload_maker->make_payload('PUSH_MESSAGE_TYPE_1', $loc_args, $share_id);
                    if (strlen($payload) <= 256)
                    {
                        $data  = array(
                                       'pqo_device_token' => $device_token['device_token'],
                                       'pqi_payload' => $payload,
                                       'pqi_time_queued' => date("Y-m-d H:i:s")
                                       );
                        $result = $this->push_queue_ios_model->insert_push_queue_iOS($data);
                    }
                    
                }
                else if ($device_token['device_type'] == 3) { // windows phone推播
                    
                    $device_token['device_token'];
                    $result= $this->wp_push_notification_maker->send_toast('Howeather' ,$user_nickname.'liked your share!','/all_share_page.xaml?type=detail&amp;share_id='.$share_id.'' ,2,$device_token['device_token']);

                    
                }
                else if ($device_token['device_type'] == 2) { // android推播
                    $payload = $this->android_push_notification_maker->make_payload('1', $share_id, $user_nickname,$notification_id);
					$data  = array(
								   'registration_id' => $device_token['device_token'],
								   'pqa_payload' => $payload,
								   'pqa_time_queued' => date("Y-m-d H:i:s")
								   );
					$result = $this->push_queue_android_model->insert_push_queue_android($data);
                }
                
            }
            
               echo json_encode(array('msg' => 'insert share likes ok',
                                      'status' => 'success'));
        }
        
        public function delete_share_likes()
        {
            // 刪除時必須提供user_id和share_id
            
            $user_id = $this->user_id;
            $share_id = $this->input->post('share_id', TRUE);

           
            
            // 防止沒有傳post value
            if(!isset($_POST["share_id"]))
            {
               echo json_encode(array('msg' => 'delete share likes post value not set',
                                      'status' => 'fail'));
                return;
            }
            
            
            
            $data = array(
                          'user_id'=>$user_id,
                          'share_id'=>$share_id
                          );
            
            $result = $this->share_likes_model->delete_share_likes($data);

             $getshare=$this->share_model->get_share(array('share_id' => $share_id));

            ////////////////////減少使用者經驗值
            if($getshare->row()->user_id!=$user_id)             
                  $this->update_user_exp($getshare->row()->user_id,-$this->share_liked);

            ////////////////////刪除通知
            
            
            $where = array(
                          'user_id_sender'=>$user_id,
                          'post_id'=>$share_id,
                           'notification_type'=> 1
                          );
            if($this->notification_model->delete_notification($where)) {
                echo json_encode(array('msg' => 'delete share likes ok, delete notification success',
                                       'status' => 'success'));
            }
            else {
                echo json_encode(array('msg' => 'delete share likes ok, but delete notification failed',
                                       'status' => 'success'));
            }
            
            
        }
        
/////////////////////////////////////////////////////
        
////////////////參考用/////////////////
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