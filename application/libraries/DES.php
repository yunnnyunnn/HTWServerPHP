<?php defined('BASEPATH') OR exit('No direct script access allowed');

class DES
{
	var $key;
	var $iv;
	public function __construct($params = array()) {
		//$this->key = $params['key'];
        $this->iv = $params['iv'];
        //$iv_size = mcrypt_get_iv_size(MCRYPT_TRIPLEDES, MCRYPT_MODE_CBC);
        //$this->iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	}
	
//	public function encrypt($str) {
//		$size = mcrypt_get_block_size(MCRYPT_TRIPLEDES, MCRYPT_MODE_CBC);
//		$str = $this->pkcs5Pad($str, $size);
//		
//		$data = mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_ENCRYPT, $this->iv);
//		return base64_encode($data);
//	}
//	
//	public function decrypt($str) {
//		$str = base64_decode($str);
//		$str = mcrypt_cbc(MCRYPT_TRIPLEDES, $this->key, $str, MCRYPT_DECRYPT, $this->iv);
//		$str = $this->pkcs5Unpad($str);
//		return $str;
//	}
    public function encrypt($plain, $key) { 
       
        //$key                = $this->PBKDF2($key, $iv, 1, 32); 
        $crypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plain, MCRYPT_MODE_CBC, $this->iv); 

        return base64_encode($crypted); 
    }
    
    public function decrypt($crypted, $key) { 
        $crypted        = base64_decode($crypted); 
        //$iv                = substr($crypted, 0, 16); 
        //$key                = $this->PBKDF2($key, $iv, 1, 32); 
        //$crypted        = substr($crypted, 16); 

        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $crypted, MCRYPT_MODE_CBC, $this->iv); 
    } 
    
    /** 
     * PHP PBKDF2 Implementation. 
     * 
     * For more information see: http://www.ietf.org/rfc/rfc2898.txt 
     * 
     * @param string $p                password 
     * @param string $s                salt 
     * @param integer $c                iteration count (use 1000 or higher) 
     * @param integer $dkl        derived key length 
     * @param string $algo        hash algorithm 
     * @return string                        derived key of correct length 
     */ 
    private function PBKDF2($p, $s, $c, $dkl, $algo = 'sha1') { 
            $kb = ceil($dkl / strlen(hash($algo, null, true))); 
            $dk = ''; 
            for($block = 1; $block <= $kb; ++$block) { 
                    $ib = $b = hash_hmac($algo, $s.pack('N', $block), $p, true); 
                    for($i = 1; $i < $c; ++$i) 
                            $ib ^= ($b = hash_hmac($algo, $b, $p, true)); 
                    $dk.= $ib; 
            } 
            return substr($dk, 0, $dkl); 
    } 

    
//	private function pkcs5Pad($text, $blocksize) {
//		$pad = $blocksize - (strlen($text) % $blocksize);
//		return $text . str_repeat(chr($pad), $pad);
//	}
//    
//	private function pkcs5Unpad($text) {
//		$pad = ord($text{strlen($text) - 1});
//		if ($pad > strlen($text)) return false;
//		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
//		return substr($text, 0, -1 * $pad);
//    }
    
}