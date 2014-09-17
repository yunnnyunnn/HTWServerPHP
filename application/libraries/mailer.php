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
        $this->mail->SMTPDebug  = 1;                     // enables SMTP debug information
        $this->mail->SMTPAuth   = true;                  // enable SMTP authentication
        $this->mail->SMTPSecure = "tls";                 // sets the prefix to the servier
        $this->mail->Host       = "email-smtp.us-east-1.amazonaws.com";      // sets AWS as the SMTP server
        $this->mail->Port       = 587;                   // set the SMTP port for the GMAIL server
        $this->mail->Username   = "AKIAI5DMYPZIXHKIOMHQ";// SES username
        $this->mail->Password   = "AkgRdaU6+hlqBc2kh2CpnpFJUx8WPSW+VdY+N6umRTGk";       // SES password   
		 
    }
 
    public function send_mail($to,$subject,$body){
        try{
			$from_name = 'HoWeather';
			$from = 'hydra200238@gmail.com';
            $this->mail->AddAddress($to);	
			$this->mail->FromName = $from_name;
   			$this->mail->From = $from;	
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