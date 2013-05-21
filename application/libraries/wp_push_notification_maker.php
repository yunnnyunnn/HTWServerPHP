<?php
class Wp_push_notification_maker()
{
    function make_push_notification($wp_url)
    {
    	 $toastMessage = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
                "<wp:Notification xmlns:wp=\"WPNotification\">" .
                   "<wp:Toast>" .
                        "<wp:Text1>" . "SendToast" . "</wp:Text1>" .
                        "<wp:Text2>" . "Text Message" . "</wp:Text2>" .
                        "</wp:Toast> " .
                "</wp:Notification>";

    // Create request to send
    $r = curl_init();
    curl_setopt($r, CURLOPT_URL,$wp_url);
    curl_setopt($r, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($r, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HEADER, true); 

    // add headers
    $httpHeaders=array('Content-type: text/xml; charset=utf-8', 'X-WindowsPhone-Target: toast',
                    'Accept: application/*', 'X-NotificationClass: 2','Content-Length:'.strlen($toastMessage));
    curl_setopt($r, CURLOPT_HTTPHEADER, $httpHeaders);

    // add message
    curl_setopt($r, CURLOPT_POSTFIELDS, $toastMessage);

    // execute request
    $output = curl_exec($r);
    curl_close($r);
    }
}

?>