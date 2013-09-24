<?php
class Android_push_notification_maker
{
	function make_notification($regID,$message,$campaigndate,$title,$description)
	{
		$fields = array(
			'registration_ids'  => $regID,
            'data' => array(
				'message' => $message,
                'campaigndate' => $campaigndate,
                'title' => $title,
                'description' => $description
                )
		);
		return json_encode($fields);
	}
}