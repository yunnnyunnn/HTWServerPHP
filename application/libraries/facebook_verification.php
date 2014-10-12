<?php
class Facebook_verification
{
    
    function verify_token_with_facebook($fb_token, $fb_id)
    {
        $verify_url = "https://graph.facebook.com/$fb_id?access_token=$fb_token";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verify_url);
        // Set so curl_exec returns the result instead of outputting it.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Get the response and close the channel.
        $response = curl_exec($ch);
        curl_close($ch);
        
        $response_array = json_decode($response, true);
        
        if (array_key_exists('email', $response_array)) {
            
            // verified
            $email = $response_array['email'];
            return $email;
            
        }
        else {
            
            // email not exist
            return FALSE;
        }
    }
    
}