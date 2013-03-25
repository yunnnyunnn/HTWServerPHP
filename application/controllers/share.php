<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    
    class Share extends My_Controller {
        
        
        public function __construct()
        {
            parent::__construct();
            $this->load->model('share_model');
            $this->load->model('share_comment_model');
            $this->load->model('share_likes_model');
            $this->load->library('S3');

        }
        public function index()
        {
            echo json_encode(array('Hello'=>'Weather'));
        }
        
/////////////////////////////////////////////////////
        
////////////////以下為對share本身的操作/////////////////
        
        public function get_share()
        {
            
            // 如果什麼都沒有傳，就全部抓
            
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
                $where['user_id'] = $get_share_user_id;
            }

            
            $share_id_max = $this->input->post('share_id_max', TRUE);
            if(isset($_POST["share_id_max"]))
            {
                
                $where['share_id <='] = $share_id_max;
            }
            
            $query = $this->share_model->get_share($where);
            
            $shares = $query->result();
            
            // 這邊開始將每一篇的comment抓下來
            foreach($shares as $share)
            {
                $share_id = $share->share_id;
                $where_sub = array('share_id'=>$share_id);
                
                $query_comment = $this->share_comment_model->get_share_comment($where_sub);
                $share->share_comment = $query_comment->result();
                
                $query_like = $this->share_likes_model->get_share_likes($where_sub);
                $share->share_likes = $query_like->result();
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
            
            
            $query = $this->share_model->get_share($where, $share_count);
            
            $shares = $query->result();
            
            // 這邊開始將每一篇的comment抓下來
            foreach($shares as $share)
            {
                $share_id = $share->share_id;
                $where_sub = array('share_id'=>$share_id);
                
                $query_comment = $this->share_comment_model->get_share_comment($where_sub);
                $share->share_comment = $query_comment->result();
                
                $query_like = $this->share_likes_model->get_share_likes($where_sub);
                $share->share_likes = $query_like->result();
            }
            
            // 將最後結果送出
            echo json_encode(array('constraints' => $where,
                                   'result' => $shares,
                                   'msg' => 'get share ok',
                                   'status' => 'success'
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
                
                //create a new bucket
                //$this->s3->putBucket("weather_bucket", S3::ACL_PUBLIC_READ);
                //move the file
                if ($this->s3->putObjectFile($fileTempName, "weather_bucket", $fileName, S3::ACL_PUBLIC_READ)) {
                    //echo "We successfully uploaded your file.";
                }else{
                    //echo "Something went wrong while uploading your file... sorry.";
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
            
            echo json_encode(array('msg' => 'insert share ok',
                                   'status' => 'success'));
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
            
            // 防止沒有傳post value
            if(!isset($_POST["share_id"]) OR !isset($_POST["share_comment_content"]))
            {
                echo json_encode(array('msg' => 'insert share comment post value not set',
                                        'status' => 'fail'));
                return;
            }
            
            
            
            $data = array(
                          'user_id'=>$user_id,
                          'share_id'=>$share_id,
                          'share_comment_content'=>$share_comment_content,
                          'share_comment_time'=>date("Y-m-d H:i:s"),
                          );
            
            $result = $this->share_comment_model->insert_share_comment($data);
            
            echo json_encode(array('msg' => 'insert share comment ok',
                                    'status' => 'success'));
        }
        
////////////////////////////////////////////////////////

////////////////以下為對share_likes的操作/////////////////

        public function insert_share_likes()
        {
            $user_id = $this->user_id;
            $share_id = $this->input->post('share_id', TRUE);
            
            // 防止沒有傳post value
            if(!isset($_POST["share_id"]))
            {
               echo json_encode(array('msg' => 'insert share likes post value not set',
                                      'status' => 'fail'));
                return;
            }
            
            
            
            $data = array(
                          'user_id'=>$user_id,
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
            
            $result = $this->share_likes_model->insert_share_likes($data);
            
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
            
               echo json_encode(array('msg' => 'delete share likes ok',
                                      'status' => 'success'));
            
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