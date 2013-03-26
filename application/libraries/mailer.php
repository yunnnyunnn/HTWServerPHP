<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
 
class Mailer {
 
    var $mail;
 
    public function __construct()
    {
        require_once('PHPMailer_v5.1/class.phpmailer.php');
 
        // the true param means it will throw exceptions on errors, which we need to catch
        $this->mail = new PHPMailer(true);
 
        $this->mail->IsSMTP(); // telling the class to use SMTP		
        $this->mail->CharSet    = "utf-8";                  // 一定要設定 CharSet 才能正確處理中文
        $this->mail->SMTPDebug  = 0;                     // enables SMTP debug information
        $this->mail->SMTPAuth   = true;                  // enable SMTP authentication
        $this->mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
        $this->mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
        $this->mail->Port       = 465;                   // set the SMTP port for the GMAIL server
        $this->mail->Username   = "";// GMAIL username
        $this->mail->Password   = "";       // GMAIL password   
		 
    }
 
    public function send_mail($to,$to_name,$subject,$body){
        try{
			$from_name = 'HoWeather';
			$from = '';
            $this->mail->AddAddress($to, $to_name);	
			$this->mail->FromName = $from_name;
   			$this->mail->From = '"'.$from.'"';	
			//$this->mail->SetFrom('"'.$from.'"', $from_name);
  			$this->mail->AddReplyTo($from, $from_name);       		
            $this->mail->Subject = $subject;
            $this->mail->Body = $body;
 
            if($this->mail->Send())
            {
				$this->mail->ClearAddresses();
				return true;
			}else
			{
				return false;
			}
 
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }
    }
}
 
/* End of file mailer.php */