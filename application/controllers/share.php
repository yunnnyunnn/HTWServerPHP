<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    
    class Share extends CI_Controller {
        
        private $user_id = NULL;
        private $user_email = NULL;
        
        public function __construct()
        {
            parent::__construct();
            
            
            
            
            $is_login = $this->session->userdata('user');
            if(!$is_login||empty($is_login['token']))
            {
                redirect('user');
            }
            else
            {
                $this->user_id = $is_login['user_id'];
                $this->user_email = $is_login['user_email'];
                $this->load->model('share_model');
                $this->load->model('share_comment_model');
                $this->load->model('share_likes_model');
            }
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

            if(isset($share_time))
            {
                
                $where['share_time >='] = date('Y-m-d H:i:s', strtotime($share_time));
            }
            
            // 如果有傳區域限制
            $share_latitude_max = $this->input->post('share_latitude_max', TRUE);
            $share_latitude_min = $this->input->post('share_latitude_min', TRUE);
            $share_longitude_max = $this->input->post('share_longitude_max', TRUE);
            $share_longitude_min = $this->input->post('share_longitude_min', TRUE);
            if(isset($share_latitude_max) && isset($share_latitude_min) && isset($share_longitude_max) && isset($share_longitude_min))
            {
                
                $where['share_latitude <='] = $share_latitude_max;
                $where['share_latitude >='] = $share_latitude_min;
                $where['share_longitude <='] = $share_longitude_max;
                $where['share_longitude >='] = $share_longitude_min;
            }
            
            
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
        
        public function insert_share()
        {
         
            $user_id = $this->user_id;

            $share_content = $this->input->post('share_content', TRUE);
            $share_weather_type = $this->input->post('share_weather_type', TRUE);
            $share_latitude = $this->input->post('share_latitude', TRUE);
            $share_longitude = $this->input->post('share_longitude', TRUE);
            
            // 防止沒有傳post value
            if(!isset($share_content) OR !isset($share_weather_type) OR !isset($share_latitude) OR !isset($share_longitude))
            {
                echo json_encode(array('result'=>'wrong post value'));
                return;
            }
            
            
            
            $data = array(
                          'user_id'=>$user_id,
                          'share_content'=>$share_content,
                          'share_weather_type'=>$share_weather_type,
                          'share_latitude'=>$share_latitude,
                          'share_longitude'=>$share_longitude,
                          'share_time'=>date("Y-m-d H:i:s"),
                          'share_likes'=>0
                          );
            
            $result = $this->share_model->insert_share($data);
            
            echo json_encode(array('result'=>$result));
        }
        
        public function delete_share()
        {
            // 刪除時必須提供user_id和share_id
            
            $user_id = $this->user_id;
            $share_id = $this->input->post('share_id', TRUE);
            
            // 防止沒有傳post value
            if(!isset($share_id))
            {
                echo json_encode(array('result'=>'wrong post value'));
                return;
            }
            
            
            
            $data = array(
                          'user_id'=>$user_id,
                          'share_id'=>$share_id
                          );
            
            $result = $this->share_model->delete_share($data);
            
            echo json_encode(array('result'=>$result));

        }
        
//////////////////////////////////////////////////////////
        
////////////////以下為對share_comment的操作/////////////////

        
        public function insert_share_comment()
        {
            $user_id = $this->user_id;
            $share_id = $this->input->post('share_id', TRUE);
            $share_comment_content = $this->input->post('share_comment_content', TRUE);
            
            // 防止沒有傳post value
            if(!isset($share_id) OR !isset($share_comment_content))
            {
                echo json_encode(array('result'=>'wrong post value'));
                return;
            }
            
            
            
            $data = array(
                          'user_id'=>$user_id,
                          'share_id'=>$share_id,
                          'share_comment_content'=>$share_comment_content,
                          'share_comment_time'=>date("Y-m-d H:i:s"),
                          );
            
            $result = $this->share_comment_model->insert_share_comment($data);
            
            echo json_encode(array('result'=>$result));
        }
        
////////////////////////////////////////////////////////

////////////////以下為對share_likes的操作/////////////////

        public function insert_share_likes()
        {
            $user_id = $this->user_id;
            $share_id = $this->input->post('share_id', TRUE);
            
            // 防止沒有傳post value
            if(!isset($share_id))
            {
                echo json_encode(array('result'=>'wrong post value'));
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
                echo json_encode(array('result'=>'like share more than once'));
                return;
            }
            
            $result = $this->share_likes_model->insert_share_likes($data);
            
            echo json_encode(array('result'=>$result));
        }
        
        public function delete_share_likes()
        {
            // 刪除時必須提供user_id和share_id
            
            $user_id = $this->user_id;
            $share_id = $this->input->post('share_id', TRUE);
            
            // 防止沒有傳post value
            if(!isset($share_id))
            {
                echo json_encode(array('result'=>'wrong post value'));
                return;
            }
            
            
            
            $data = array(
                          'user_id'=>$user_id,
                          'share_id'=>$share_id
                          );
            
            $result = $this->share_likes_model->delete_share_likes($data);
            
            echo json_encode(array('result'=>$result));
            
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