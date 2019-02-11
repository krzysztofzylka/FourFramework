<?php
return $this->crypt = new class($this->core){
	protected $core;
	private $method = 'AES-256-CBC';
	public $salt = '0123456789012345';
	public function __construct($obj){
		$this->core = $obj;
	}
	//crypt string
	public function crypt(string $string, $hash=null) : string{
		$this->core->returnError();
		return base64_encode(
			openssl_encrypt(
				$string,
				$this->method,
				$hash,
				0,
				$this->salt
			)
		);
	}
	//decrypt string
	public function decrypt(string $string, $hash=null) : string{
		$this->core->returnError();
		return openssl_decrypt(
			base64_decode($string),
			$this->method,
			$hash,
			0,
			$this->salt
		);
	}
}
?>