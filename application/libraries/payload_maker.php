<?php
class Payload_maker
{
	
	function make_payload($loc_key, $loc_args, $post, $action_loc_key = 'null', $badge = 0, $sound = 'default')
    {
        
        
        $payload = "{\"aps\":{\"alert\":{\"loc-key\":\"$loc_key\",\"loc-args\":\"$loc_args\",\"action-loc-key\":$action_loc_key},\"badge\":$badge,\"sound\":\"$sound\"},\"post\":$post,}";
        
        return $payload;
    
    }
    
}