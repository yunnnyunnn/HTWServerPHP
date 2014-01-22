<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends My_Controller {
	
	public function __construct()
	{
		parent::__construct();	
    $this->load->model('user_model');
		$this->load->model('user_medal_model');
		$this->load->model('share_model');
        $this->load->model('answer_model');
        $this->load->model('location_log_model');
        $this->load->model('device_model');
        $this->load->model('notification_model');

	}
	public function index()
	{
		//
		
		//$this->get_one_user();
	}

  public function update_user_status()
  {
     $status = '';
     $msg = '';
     $echo_data = array();
     $user_id = $this->user_id;
     $edit_status=$this->input->post('user_status',TRUE);

      if(!isset($_POST["user_status"]))
      {
          echo json_encode(array('msg' => 'user_status post value not set',
                                 'status' => 'fail'));
          return;
      }

      $where=array('user_id' => $user_id );
      $updatefield=array('user_status' => $edit_status);      
        
      if($this->user_model->update_user($where,$updatefield))
      {
         $status='ok';
         $msg='update user status success';
      }
      else
      {
        $status='fail';
        $msg='update user status fail';
      }

      echo json_encode(array('status' => $status,'msg'=>$msg ));
  }

  public function update_user_nickname()
  {
     $status = '';
     $msg = '';
     $echo_data = array();
     $user_id = $this->user_id;
     $edit_name=$this->input->post('user_nickname',TRUE);

      if(!isset($_POST["user_nickname"]))
      {
          echo json_encode(array('msg' => 'user_nickname post value not set',
                                 'status' => 'fail'));
          return;
      }

      $where=array('user_id' => $user_id );
      $updatefield=array('user_nickname' => $edit_name);      
        
      if($this->user_model->update_user($where,$updatefield))
      {
         $status='ok';
         $msg='update user status success';
      }
      else
      {
        $status='fail';
        $msg='update user status fail';
      }

      echo json_encode(array('status' => $status,'msg'=>$msg ));
  }

  public function update_user_medal()
  {
     $status = '';
     $msg = '';
     $echo_data = array();
     $user_id = $this->user_id;
     $edit_medal=$this->input->post('user_medal',TRUE);
    

      if(!isset($_POST["user_medal"]))
      {
          echo json_encode(array('msg' => 'user_medal post value not set',
                                 'status' => 'fail'));
          return;
      }

      $where=array('user_id' => $user_id );
      $updatefield=array('user_medal' => $edit_medal);    

      ///////判斷是否拿過該medal
      $medal_checker_where=array('user_id' => $user_id,'medal_id'=>$edit_medal );
      $medal_checker=$this->user_medal_model->get_user_medal('*',$medal_checker_where);
      if($medal_checker->num_rows()>0)
      {
          //已得過medal,可以換
          if($this->user_model->update_user($where,$updatefield))
          {
              $status='ok';
              $msg='update user medal success';
          }
          else
          {
              $status='fail';
              $msg='update user medal fail';
          }
      }
      else
      {
          //沒得過medal,不給換
          //$this->user_medal_model->insert_user_medal($medal_checker_where);
          $status='fail';
          $msg='no such medal';
      }
      

      echo json_encode(array('status' => $status,'msg'=>$msg ));
  }
	
	public function get_one_user()
	{
		$status = '';
		$msg = '';
		$echo_data = array();
		$user_id = $this->input->post('user_id',TRUE);
		//$user_id=1368326288;
		if(empty($user_id)||!is_numeric($user_id))
		{
			$status = 'fail';
			$msg = 'missing user id';
		}
		else
		{
			$field = array('*');
			$where_data = array('user.user_id'=>$user_id);
			$user_data = $this->user_model->get_user($field,$where_data);
			if($user_data->num_rows()>0)
			{
				$field = array('user_id');
				$where_data = array('user_exp >'=>$user_data->row()->user_exp);
				$user_rank = $this->user_model->get_user($field,$where_data);
				
				$where_data = array('user.user_id'=>$user_id);
				$user_share = $this->share_model->get_share($where_data);
                
				$user_answer = $this->answer_model->get_answer('*', $where_data);
		
				$status = 'ok';
				$msg = 'ok';
				$echo_data['user_share_count'] = $user_share->num_rows();
                $echo_data['user_answer_count'] = $user_answer->num_rows();
				$echo_data['user_rank'] = $user_rank->num_rows()+1;
                $user_data->row()->user_password = '*';
				$echo_data['userdata'] = $user_data->result();
			}
			else
			{
				$status = 'fail';
				$msg = 'no such user';
			}			
		}
		$echo_data['status'] = $status;
		$echo_data['msg'] = $msg;
		echo json_encode($echo_data);	
	}

  public function get_user_medal()
  {
      $echo_data = array();
      $user_id = $this->input->post('user_id',TRUE);
      //$user_id=1369531833;
      if(empty($user_id)||!is_numeric($user_id))
      {
        $status = 'fail';
        $msg = 'missing user id';
      }
      else
      {
        $field = array('*');
        $where_data = array('user_id'=>$user_id);
        $user_medal_data = $this->user_medal_model->get_user_medal($field,$where_data);
        if($user_medal_data->num_rows()>0)
        {

          $status = 'ok';
          $msg = 'ok';
          $echo_data['result'] = $user_medal_data->result();

        }
        else
        {
          $status = 'fail';
          $msg = 'no such user';
        }     
      }
      $echo_data['status'] = $status;
      $echo_data['msg'] = $msg;
      echo json_encode($echo_data); 
    }
  
    
    public function insert_location_log()
    {
        
        $user_id = $this->user_id;
        
        $location_latitude = $this->input->post('location_latitude', TRUE);
        $location_longitude = $this->input->post('location_longitude', TRUE);
        
        // 防止沒有傳post value
        if(!isset($_POST["location_latitude"]) OR !isset($_POST["location_longitude"]))
        {
            echo json_encode(array('msg' => 'insert location post value not set',
                                   'status' => 'fail'));
            return;
        }
        
        
        $data = array(
                      'user_id'=>$user_id,
                      'location_latitude'=>$location_latitude,
                      'location_longitude'=>$location_longitude,
                      'location_log_time'=>date("Y-m-d H:i:s"),
                      );
        
        $result = $this->location_log_model->insert_location_log($data);
        
        echo json_encode(array('msg' => 'update user location ok',
                               'status' => 'ok'));
    }
    public function get_latest_location()
	{
		$user_id = $this->input->post('user_id',TRUE);
        $where = array(
            'user_id' => $user_id
        );
        $query = $this->location_log_model->get_location_log($where,'*',1);
		if($query->num_rows()>0)
		{
			$status = 'ok';
		}
		else
		{
			$status = 'fail';
		}
		echo json_encode(array('status' => $status,'result' => $query->result()));
	}
	
    public function update_device_token()
    {
        $user_id = $this->user_id;
        $device_type = $this->input->post('device_type', TRUE);
        $device_token = $this->input->post('device_token', TRUE);
        $device_id = $this->input->post('device_id', TRUE);


        // 防止沒有傳post value
        if(!isset($_POST["device_type"]) OR !isset($_POST["device_token"]) OR !isset($_POST["device_id"]))
        {
            echo json_encode(array('msg' => 'wrong post value',
                                   'status' => 'fail'));
            return;
        }
        
        $where = array(
                            'user_id' => $user_id,
                            'device_type' => $device_type,
                       'device_id' => $device_id
                       );
        
        $data = array(
                            'device_token' => $device_token
                      );
        
        // 先將一樣的token清掉
        $this->device_model->update_device(array('device_token' => $device_token), array('device_token' => ''));
        
        if ($this->device_model->update_device($where, $data)) {
            echo json_encode(array('msg' => 'successfully update device token',
                                   'status' => 'ok'));
            return;
        }
        else {
            echo json_encode(array('msg' => 'database update wrong',
                                   'status' => 'fail'));
            return;
        }
        
        
    }

    function get_notification_stream()
    {
        $user_id = $this->user_id;
        $where = array();
        $where['user_id_receiver']=$user_id;
        
        // 指定這次抓的串流從哪一篇開始
        $notification_id_max = $this->input->post('notification_id_max', TRUE);
        if(isset($_POST["notification_id_max"]))
        {
            
            $where['notification_id <'] = $notification_id_max;
        }
        
        
        // 指定一次抓幾篇，沒有指定的話預設值是25篇
        $notification_count = 25;
        if (isset($_POST["notification_count"])) {                
            $notification_count = $this->input->post('notification_count', TRUE);
        }

         //$field = array('*', 'user.user_nickname');
        $query = $this->notification_model->get_notification($where, $notification_count);
        
        $notifications = $query->result();
        
        foreach ($notifications as $notification) {
            $notification_type = $notification->notification_type;
            if ($notification_type < 2) { // shares notification
                $where_share = array ('share_id' => $notification->post_id);
                
                $query = $this->share_model->get_share($where_share, 'share_weather_type, share_photo_url', 1);
                $shares = $query->result();
                
                //if ($query->num_rows()>0){
                    $share = $shares[0];
                    
                    $notification->notification_share_photo_url = $share->share_photo_url;
                    $notification->notification_share_weather_type = $share->share_weather_type;
                //}
                
            }
            else { // asks notification
                
            }
        }

           // 將最後結果送出
        echo json_encode(array('constraints' => $where,
                               'result' => $notifications,
                               'msg' => 'get notification ok',
                               'status' => 'success'
                               ));
    }
	
    function get_notification_count()
	{
		$user_id = $this->user_id;
        $where = array();
        $where['user_id_receiver'] = $user_id;
        $notification_time = $this->input->post('notification_time', TRUE);
		//$notification_time = date('Y-m-d H:i:s');
		$where['notification_is_record'] = '0';
        if(isset($_POST["notification_time"]))
        { 
            $where['notification_time >='] = $notification_time;
        }
		$count = $this->notification_model->get_notifitcation_count($where);
		echo json_encode(array('count' => $count)); 
	}
	
    function set_notification_is_record()
    {
        
        $notification_is_record = $this->input->post('notification_is_record', TRUE);
        $notification_id = $this->input->post('notification_id', TRUE);
        
        // 防止沒有傳post value
        if(!isset($_POST["notification_is_record"]) OR !isset($_POST["notification_id"]))
        {
            echo json_encode(array('msg' => 'wrong post value',
                                   'status' => 'fail'));
            return;
        }
        
        $where = array(
                       'notification_id' => $notification_id
                       );
        
        $data = array(
                      'notification_is_record' => $notification_is_record
                      );
        
        
        
        if ($this->notification_model->update_notification($data, $where)) {
            echo json_encode(array('msg' => 'successfully update notification is read',
                                   'status' => 'ok'));
            return;
        }
        else {
            echo json_encode(array('msg' => 'database update wrong',
                                   'status' => 'fail'));
            return;
        }
    }
    
    function set_similiar_notification_is_record()
    {
        $user_id = $this->user_id;
        $notification_is_record = $this->input->post('notification_is_record', TRUE);
        $notification_id = $this->input->post('notification_id', TRUE);
        $post_id = $this->input->post('post_id', TRUE);
        $notification_type = $this->input->post('notification_type', TRUE);

        // 防止沒有傳post value
        if(!isset($_POST["notification_is_record"]) OR !isset($_POST["notification_id"]) OR !isset($_POST["post_id"]) OR !isset($_POST["notification_type"]))
        {
            echo json_encode(array('msg' => 'wrong post value',
                                   'status' => 'fail'));
            return;
        }
        
        $where = array(
                       'post_id' => $post_id,
                       'notification_type' => $notification_type,
                       'user_id_receiver' => $user_id
                       );
        
        $data = array(
                      'notification_is_record' => $notification_is_record
                      );
        
        
        
        if ($this->notification_model->update_notification($data, $where)) {
            echo json_encode(array('msg' => 'successfully update notification is read',
                                   'status' => 'ok'));
            return;
        }
        else {
            echo json_encode(array('msg' => 'database update wrong',
                                   'status' => 'fail'));
            return;
        }
    }
    
    
	function signout()
    {
		$user_id = $this->user_id;
        $status = '';
		$msg = '';
        $device_id = $this->input->post('device_id',TRUE);
        
        if (empty($device_id)||!is_numeric($device_id)) {
            $msg = 'wrong device';
			$status = 'fail';
        }
        else 
		{
            $where = array(
				'device_id'=>$device_id,
				'user_id'=>$user_id
			);
            if ($this->device_model->delete_device($where)) {
                $msg = 'sign out success';
                $status = 'ok';
            }
            else {
                $msg = 'db wrong when signing out';
                $status = 'fail';
            } 
        }
        $echo_data['status'] = $status;
		$echo_data['msg'] = $msg;
		echo json_encode($echo_data);
    }
	
}