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
		$insert_news_array = array('news_title' => $this -> input -> post('news_title', TRUE), 'news_content' => $this -> input -> post('news_content', TRUE), 'news_latitude' => $this -> input -> post('news_latitude', TRUE), 'news_longtitude' => $this -> input -> post('news_longtitude', TRUE), 'news_likes' => 0, 'news_distance' => $this -> input -> post('news_distance', TRUE), 'news_time' => date('Y-m-d H:i:s'));

		if ($this -> news_model -> insert_news($insert_news_array)) {
			$status = "ok";
			$msg = "Insert Success!";
			$news_id = array();
			$all_news = $this -> news_model -> get_news($news_id);
		} else {
			$status = 'fail';
			$msg = "Insert Fail!";
		}

		echo json_encode(array('status' => $status, 'all_news', $all_news -> result(), 'msg' => $msg));
	}

	function update_news_submit() {
		$update_news_id = $this -> input -> post('news_id', TRUE);
		$update_news_array = array('news_title' => $this -> input -> post('news_title', TRUE), 'news_content' => $this -> input -> post('news_content', TRUE), 'news_latitude' => $this -> input -> post('news_latitude', TRUE), 'news_longtitude' => $this -> input -> post('news_longtitude', TRUE), 'news_likes' => 0, 'news_distance' => $this -> input -> post('news_distance', TRUE), 'news_time' => date('Y-m-d H:i:s'));

		if ($this -> news_model -> update_news($update_news_array, array('news_id', $update_news_id))) {
			$status = 'ok';
			$msg = 'Update News Succeed!';
			$updated_news = $this -> news_model -> get_news_and_comment($update_news_id);
		} else {
			$status = 'fail';
			$msg = 'Update News Fail';
		}
		echo json_encode(array('status' => $status, 'msg' => $msg, 'updated_news' => $updated_news -> result()));
	}

	function get_news() {
		$news_id = $this -> uri -> segment(3);
		if (is_numeric($news_id) && empty($news_id))
			$all_news = $this -> news_model -> get_news_and_comment(array('news_id' => $news_id));
		else {
			$all_news = $this -> news_model -> get_news_and_comment(array());
		}

		if ($all_news) {
			$status = 'ok';
			$msg = 'Get News Success!';
		} else {
			$status = 'fail';
			$msg = 'get news fail!';
		}
		echo json_encode(array('status' => $status, 'msg' => $msg, $all_news -> result()));
	}

	function delete_news() {
		$news_id = $this -> input -> post('news_id');
		if (is_numeric($news_id) && !empty($news_id)) {
			if ($this -> news_model -> delete_news($news_id)) {
				if ($this -> news_comment_model -> delete_comment(array('news_id' => $news_id))) {
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
		$news_comment_content = $this -> input -> post('$news_comment_content', TRUE);
		$news_comment_time = date('Y-m-d H:i:s');

		if (!is_numeric($news_id) && !empty($news_id) && !is_numeric($user_id) && !empty($user_id)) {
			$insert_comment_data = array('$user_id' => $user_id, '$news_id' => $news_id, '$news_comment_content' => $news_comment_content, '$news_comment_time' => $news_comment_time, );

			$inserted_id = $this -> input -> news_comment_model -> insert_comment($insert_comment_data);

			if ($inserted_id) {
				$status = 'ok';
				$msg = 'Insert Comment Succeed!';
				$inserted_comment = $this -> news_comment_model -> get_comment(array('news_id', $inserted_id));
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

	function delete_news_commment() {
		$news_comment_id = $this -> input -> post('news_commment_id', TRUE);
		if (is_numeric($news_comment_id) && !empty($news_comment_id)) {
			if ($this -> news_comment_model -> delete_comment($news_comment_id)) {
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
		if(is_numeric($user_id)&&empty($user_id))
		{
			$update_news_comment_array=array(
			$news_comment_content=>$this->input->post('$news_comment_content',TRUE),
			$news_comment_time=>date('Y-m-d H:i:s')
			
			
			);
			if($this->news_content_model->update_news_comment($update_news_comment_array,array('news_comment_id',$updated_news_comment_id)))
			{
				$status='ok';
				$msg='update success!';
				$updated_news_comment=$this->$news_comment_model->get_comment('news_comment_id',$updated_news_comment_id);
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
		echo json_encode(array('status'=>$status,'msg'=>$msg,'updated_news_comment'=>$updated_news_comment));
	}

	function insert_news_like_submit() {
		$news_id = $this -> input -> post('news_id', TRUE);
		$user_id = $this -> input -> post('user_id', TRUE);
		$liked_news = $this -> news_model -> get_news(array('news_id', $news_id));

		$insert_news_like_array = array('user_id' => $user_id, 'news_id' => $news_id);

		if ($this -> news_likes_model -> insert_like($insert_news_like_array)) {
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

		if (is_numeric($user_id) && !empty($user_id) && is_numeric($news_id) && !empty($news_id)) {
			if ($this -> news_likes_model -> delete_like($delete_field)) {
				$status = 'ok';
				$msg = 'Delete Likes Succeed!';
			} else {
				$status = 'fail';
				$msg = 'Delete fail!';
			}
		}

		echo json_encode(array('status' => $status, 'msg' => $msg));
	}

}
?>