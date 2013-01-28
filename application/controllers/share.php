<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    
    class Share extends CI_Controller {
        
        public function __construct()
        {
            parent::__construct();
            $this->load->model('share_model');
        }
        public function index()
        {
            echo json_encode(array('Hello'=>'Weather'));
        }
        
        public function get_share()
        {
            
        }
        
        public function insert_share()
        {
            $user_id = $this->input->post('user_id', TRUE);
            $share_content = $this->input->post('share_content', TRUE);
            $share_weather_type = $this->input->post('share_weather_type', TRUE);
            $share_latitude = $this->input->post('share_latitude', TRUE);
            $share_longitude = $this->input->post('share_longitude', TRUE);
            
            // 防止沒有傳post value
            if(empty($user_id) OR empty($share_content) OR empty($share_weather_type) OR empty($share_latitude) OR empty($share_longitude))
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