<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class news extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this -> load -> model('news_model');
		$this -> load -> model('news_comment_model');
		$this -> load -> model('news_likes_model');
	}

	function index() {

	}

	function insert_news_submit() {
		//while(true)
                //{
		$news_title=rand(1,2000);//$this -> input -> post('news_title', TRUE);
		$news_content=$this -> input -> post('news_content', TRUE);
		
		//$news_latitude=$this -> input -> post('news_latitude', TRUE);
		//$news_longitude=$this -> input -> post('news_longtitude', TRUE);
		$news_latitude=rand (-90,90).'.'.rand (1000,9999);
		$news_longitude=rand (-180,180).'.'.rand (1000,9999);
		$news_distance=rand(10,900);//$this -> input -> post('news_distance', TRUE);
		$insert_news_array = array('news_title' => $news_title,
		 'news_content' => $news_content,
		 'news_latitude' => $news_latitude, 
		 'news_longitude' =>$news_longitude ,
		 'news_likes' => 0, 
		 'news_distance' => $news_distance,
		 'news_time' =>  date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) - (60 * 60 * rand (1,10)))
		 );

		if ($this -> news_model -> insert_news($insert_news_array)) {
			$status = "ok";
			$msg = "Insert Success!";
			$news_id = array();
			$all_news = $this -> news_model -> get_news($news_id);
		} else {
			$status = 'fail';
			$msg = "Insert Fail!";
		}
               //}

		echo json_encode(array('status' => $status, 'all_news', $all_news -> result(), 'msg' => $msg));
	}

	function update_news_submit() {
		$update_news_id = $this -> input -> post('news_id', TRUE);
		$update_news_array = array('news_title' => $this -> input -> post('news_title', TRUE), 'news_content' => $this -> input -> post('news_content', TRUE), 'news_latitude' => $this -> input -> post('news_latitude', TRUE), 'news_longtitude' => $this -> input -> post('news_longtitude', TRUE), 'news_likes' => 0, 'news_distance' => $this -> input -> post('news_distance', TRUE), 'news_time' => date('Y-m-d H:i:s'));

		if ($this -> news_model -> update_news($update_news_array, array('news_id'=>$update_news_id))) {
			$status = 'ok';
			$msg = 'Update News Succeed!';
			$updated_news = $this -> news_model -> get_news_and_comment( array('news.news_id'=>$update_news_id));
		} else {
			$status = 'fail';
			$msg = 'Update News Fail';
		}
		echo json_encode(array('status' => $status, 'msg' => $msg, 'updated_news' => $updated_news -> result()));
	}

	function get_news() {
		$news_id = $this->input->post('news_id',TRUE);
                
 		//echo date('Y-m-d H:i:s').'<br/>';
		if (!empty($news_id))
		{
			$all_news = $this -> news_model -> get_news_and_comment(array('news.news_id' => $news_id));
			
		}
			
		else {
			$time=time();
 
                        $limited_hour=$this->input->post('limited_hour',TRUE);
                       
			$limit_time=date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s")) - (60 * 60*$limited_hour));

                        if($limited_hour)
                        { 
			   $all_news = $this -> news_model -> get_news_and_comment(array('news_time >='=>$limit_time));
                           foreach($all_news->result() as $news)
                           {
                               $news_likes=$this->news_likes_model->get_likes(array('news_likes.news_id'=>$news->news_id));
                               $news->user_likes=$news_likes->result();
                           }
                        } 
                        else
                        {
                            $all_news = $this -> news_model -> get_news_and_comment(array()); 
                           foreach($all_news->result() as $news)
                           {
                               $news_likes=$this->news_likes_model->get_likes(array('news_likes.news_id'=>$news->news_id));
                               $news->user_likes=$news_likes->result();
                           }
			//$all_news_comment=$this->news_comment_model->get_comment(array());
                        }
		}

		if ($all_news) {
                        
			
			$now_all_news=$all_news->result();
			foreach($now_all_news as $key => $news)
			{
                                $news_id=$news->news_id;
                                $all_news_comment=$this->news_comment_model->get_comment(array('news_comment.news_id'=>$news_id));
                                $news->news_comments_list=$all_news_comment->result();
				
				//unset($now_all_news[$key]);
				//		echo count($now_all_news).'<br>';
				//$news_comment_array=array();
				/*foreach( $now_news_comment_object as $key=>$news_comment)
				{
					if($news_comment->news_id==$news_id)
					{
						$news_comment_array[]=$news_comment;
						
						unset($now_news_comment_object[$key]);
						echo count($now_news_comment_object).'<br>';
						
					}
				}
				$news->news_comment_array=$news_comment_array;*/
			}
			$status = 'ok';
			$msg = 'Get News Success!';
		} else {
			$status = 'fail';
			$msg = 'get news fail!';
		}
		echo json_encode(array('status' => $status, 'msg' => $msg, 'share_list'=>$all_news -> result()));
		
	}

	function delete_news() {
		$news_id = $this -> input -> post('news_id');
                //$user_id=$this->input->post('user_id');
                $delete_array=array('news_id'=>$news_id);
		
		if (is_numeric($news_id) && !empty($news_id)) {
			if ($this -> news_model -> delete_news($delete_array)) {
				if ($this -> news_comment_model -> delete_comment(array('news_comment.news_id' => $news_id))) {
					$status = 'ok';
					$msg = 'Delete Succeed!';
				} else {
					$status = 'fail';
					$msg = 'Delete relative comment fail';
				}

			} else {
				$status = 'fail';
				$msg = 'Delete fail';
			}
		}
		echo json_encode(array('status' => $status, 'msg' => $msg));
	}

	function insert_news_comment_submit() {

		$user_id = $this -> input -> post('user_id', TRUE);
		$news_id = $this -> input -> post('news_id', TRUE);
		$news_comment_content = $this -> input -> post('news_comment_content', TRUE);
		$news_comment_time = date('Y-m-d H:i:s');

		if (is_numeric($news_id) && !empty($news_id) && is_numeric($user_id) && !empty($user_id)) {
			$insert_comment_data = array('user_id' => $user_id, 'news_id' => $news_id, 'news_comment_content' => $news_comment_content, 'news_comment_time' => $news_comment_time, );

			$inserted_id = $this  -> news_comment_model -> insert_comment($insert_comment_data);

			if ($inserted_id) {
				$status = 'ok';
				$msg = 'Insert Comment Succeed!';
				$inserted_comment = $this -> news_comment_model -> get_comment(array('news_comment_id'=> $inserted_id));
				
			} else {
				$status = 'fail';
				$msg = 'insert comment fail';
			}
		} else {
			$status = 'fail';
			$msg = 'field_error';
		}

		echo json_encode(array('status' => $status, 'msg' => $msg, 'inserted_comment' => $inserted_comment -> result()));
	}

	function delete_news_comment() {
		$news_comment_id = $this -> input -> post('news_commment_id', TRUE);
		if (is_numeric($news_comment_id) && !empty($news_comment_id)) {
			if ($this -> news_comment_model -> delete_comment(array('news_comment_id'=> $news_comment_id))) {
				$status = 'ok';
				$msg = 'Delete News Comment Succeed!';
			} else {
				$status = 'fail';
				$msg = 'delete fail';
				
			}
		}

		echo json_encode(array('status' => $status, 'msg' => $msg));
	}
	
	function update_news_comment()
	{
		$updated_news_comment_id=$this->input->post('news_comment_id',TRUE);
		$user_id=$this->input->post('user_id',TRUE);
		$news_comment_content=$this->input->post('$news_comment_content',TRUE);
		if(is_numeric($user_id)&&!empty($user_id))
		{
			$update_news_comment_array=array(
			'news_comment_content'=>$news_comment_content,
			'news_comment_time'=>date('Y-m-d H:i:s')
			
			
			);
			if($this->news_comment_model->update_news_comment($update_news_comment_array,array('news_comment_id'=>$updated_news_comment_id)))
			{
				$status='ok';
				$msg='update success!';
				$updated_news_comment=$this->news_comment_model->get_comment(array('news_comment_id'=>$updated_news_comment_id));
			}
			else {
				$status='fail';
				$msg='update fail';
			}
		}
		else {
			$status='fail';
			$msg='wrong news coment';
		}
		echo json_encode(array('status'=>$status,'msg'=>$msg,'updated_news_comment'=>$updated_news_comment->result()));
	}

	function insert_news_like_submit() {
		$news_id = $this -> input -> post('news_id', TRUE);
		$user_id = $this -> input -> post('user_id', TRUE);
		$liked_news = $this -> news_model -> get_news(array('news_id'=>  $news_id));

		$insert_news_like_array = array('user_id' => $user_id, 'news_id' => $news_id);

		if (($this -> news_likes_model -> insert_like($insert_news_like_array))) {
			$update_news_array = array('news_likes' => $liked_news -> row() -> news_likes + 1);
			if ($this -> news_model -> update_news($update_news_array, array('news_id' => $news_id))) {
				$status = 'ok';
				$msg = 'Insert Like OK!';
			}
		} else {
			$status = 'fail';
			$msg = 'Insert Like Fail!';
		}

		echo json_encode(array('status' => $status, 'msg' => $msg));
	}

	function delete_news_like() {
		$user_id = $this -> input -> post('user_id', TRUE);
		$news_id = $this -> input -> post('news_id', TRUE);
		$delete_field = array('news_id' => $news_id, 'user_id' => $user_id);
		$liked_news = $this -> news_model -> get_news(array('news_id'=>  $news_id));

		if (is_numeric($user_id) && !empty($user_id) && is_numeric($news_id) && !empty($news_id)) {
			if ($this -> news_likes_model -> delete_like($delete_field)) {
				$update_news_array = array('news_likes' => $liked_news -> row() -> news_likes - 1);
			if ($this -> news_model -> update_news($update_news_array, array('news_id' => $news_id))) {
				$status = 'ok';
				$msg = 'Delete Like OK!';
			}
				else
				{$status='fail';
				
				}
			} else {
				$status = 'fail';
				$msg = 'Delete fail!';
			}
		}

		echo json_encode(array('status' => $status, 'msg' => $msg));
	}
	
	
	/*---------------------------------test_area------------------------------------------------*/
	function insert_news()
	{
		$this->load->view('insert_news_view');
	}

}
?>