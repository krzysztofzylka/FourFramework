<?php
return $this->security = new class($this->core){
	protected $core;
	public $sessionName = 'SessProtToken';
	public $token;
	public $version = '1.0';
	public function __construct($obj){
		$this->core = $obj;
	}
	public function createToken() : string{
		$library = $this->core->library;
		$this->token = $library->crypt->hash($library->string->generateString(25), 'md5');
		$_SESSION[$this->sessionName] = $this->token;
		return $this->token;
	}
	public function checkToken(string $token) : bool{
		if(!isset($_SESSION[$this->sessionName]))
			return $this->core->returnError(1, 'no find session token');
		if($token == $_SESSION[$this->sessionName])
			return true;
		else return false;
	}
	public function isXMLHttpRequest() : bool{
		return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
	}
};
?>