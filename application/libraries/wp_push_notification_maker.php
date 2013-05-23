<?php
class Wp_push_notification_maker
{


    private $debug_mode = false;
 
   
 
  
 
   public function send_toast($title, $message, $priority = 2,$url)
    {
        $msg = "< ?xml version=\"1.0\" encoding=\"utf-8\"?>" .
            "<wp:Notification xmlns:wp=\"WPNotification\">" .
                "<wp :Toast>" .
                    "<wp :Text1>" . $title . "</wp :Text1>" .
                    "<wp :Text2>" . $message . "</wp :Text2>" .
                "</wp:Toast>" .
            "</wp:Notification>";
 
     $result= $this->_send_push(array(
                                      'X-WindowsPhone-Target: toast',
                                      'X-NotificationClass:'.$priority, 
                                      ), $msg,$url);
     print_r($result);
    }
 
    private function _send_push($headers, $msg,$url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, true); 
        curl_setopt($ch, CURLOPT_HTTPHEADER,    // Add these headers to all requests
            $headers + array(
                            'Content-Type: text/xml',
                            'Accept: application/*'
                            )
            ); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
 
        if ($this->debug_mode)
        {
            curl_setopt($ch, CURLOPT_VERBOSE, $this->debug_mode);
            curl_setopt($ch, CURLOPT_STDERR, fopen('debug.log','w'));
        }
        $output = curl_exec($ch);
        curl_close($ch);
 
        return array(
            'X-SubscriptionStatus'     => $this->_get_header_value($output, 'X-SubscriptionStatus'),
            'X-NotificationStatus'     => $this->_get_header_value($output, 'X-NotificationStatus'),
            'X-DeviceConnectionStatus' => $this->_get_header_value($output, 'X-DeviceConnectionStatus')
            );
    }
 
    private function _get_header_value($content, $header)
    {
        return preg_match_all("/$header: (.*)/i", $content, $match) ? $match[1][0] : "";
    }
}

?>