<?php
return $this->crypt = new class(){ 
	public $version = '1.0c'; 
	private $method = 'AES-256-CBC'; 
	public $salt = '0123456789012345'; 
	public $hashAlgorithm = ['md5', 'sha256', 'pbkdf2', 'sha512', 'crc32', 'ripemd256', 'snefru', 'gost']; 
	public function crypt(string $string, $hash=null) : string{
		core::setError();
		if(!@function_exists(openssl_encrypt)) 
			die('Error use function crypt (library crypt), you must run ssl module in server'); 
		return base64_encode(openssl_encrypt($string, $this->method, $hash, 0, $this->salt));
	}
	public function decrypt(string $string, $hash=null) : string{
		core::setError(); 
		if(!@function_exists(openssl_encrypt)) 
			die('Error use function decrypt (library crypt), you must run ssl module in server'); 
		return openssl_decrypt(base64_decode($string), $this->method, $hash, 0, $this->salt); 
	}
	public function hash(string $string, string $algorithm='pbkdf2') : string{ 
		core::setError(); 
		$return = '${type}${hash}'; 
		switch($algorithm){ 
			case '001':
			case 'md5':
				$return = str_replace('{type}', '001', $return);
				$return = str_replace('{hash}', md5($string), $return);
				break;
			case '002':
			case 'sha256':
				$return = str_replace('{type}', '002', $return);
				$return = str_replace('{hash}', hash('sha256', $string), $return);
				break;
			case '003':
			case 'pbkdf2':
				if(!function_exists("hash_pbkdf2"))
					return $this->core->returnError(1, 'unknown function');
				$return = str_replace('{type}', '003', $return);
				$return = str_replace('{hash}', hash_pbkdf2("sha256", $string, $this->salt, 4096, 20), $return);
				break;
			case '004':
			case 'sha512':
				$return = str_replace('{type}', '004', $return);
				$return = str_replace('{hash}', hash('sha512', $string), $return);
				break;
			case '005':
			case 'crc32':
				$return = str_replace('{type}', '005', $return);
				$return = str_replace('{hash}', hash('crc32', $string), $return);
				break;
			case '006':
			case 'ripemd256':
				$return = str_replace('{type}', '006', $return);
				$return = str_replace('{hash}', hash('ripemd256', $string), $return);
				break;
			case '007':
			case 'snefru':
				$return = str_replace('{type}', '007', $return);
				$return = str_replace('{hash}', hash('snefru', $string), $return);
				break;
			case '008':
			case 'gost':
				$return = str_replace('{type}', '008', $return);
				$return = str_replace('{hash}', hash('gost', $string), $return);
				break;
		}
		return $return; 
	}
	public function hashCheck(string $string, string $hash) : bool{ 
		core::setError();
		$algoritm = substr($hash, 1, 3); 
		$string = $this->hash($string, $algoritm); 
		return $string===$hash; 
	}
	public function isBase64(string $crypt) : bool{ 
		core::setError();
		return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $crypt); 
	}
}
?>