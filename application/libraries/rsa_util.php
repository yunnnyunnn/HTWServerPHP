<?php
class Rsa_util
{
	private function _get_private_key()
    {
		$priv_key = PRIVATE_KEY;	
		return openssl_pkey_get_private($priv_key);		
	}
    
	private function _get_public_key()
    {
		$public_key = PUBLIC_KEY;
		return openssl_pkey_get_public($public_key);		
	}
	
	public function public_encrypt($data)
	{
		if(!is_string($data)){
				return null;
		}			
		return openssl_public_encrypt($data,$encrypted,self::_get_public_key())? base64_encode($encrypted) : null;
	}
   
   
	public function priv_decrypt($encrypted)
	{
		if(!is_string($encrypted)){
				return null;
		}
		return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, self::_get_private_key()))? $decrypted : null;
	}
}