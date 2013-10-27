<?php
class Android_push_notification_maker
{
	function make_payload($PUSH_MESSAGE_TYPE,$post_id,$user_nickname,$notification_id,$comment = '')
	{
		$message =  array(
			'PUSH_MESSAGE_TYPE' => $PUSH_MESSAGE_TYPE,
			'post_id' => $post_id,
			'user_nickname' => $user_nickname,
			'comment' =>$comment,
			'notification_id' => $notification_id
			);
		return json_encode($message);
	}
}