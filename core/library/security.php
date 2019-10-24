<?php
return $this->security = new class(){ 
	public $version = '1.0a'; 
	public $sessionName = 'SessProtToken'; 
	public $token; 
	public function createToken($name=null) : string{ 
		core::setError(); 
		$name = $name??$this->sessionName; 
		$this->token = core::$library->crypt->hash(core::$library->string->generateString(25), 'md5'); 
		$_SESSION[$name] = $this->token; 
		return $this->token; 
	}
	public function checkToken(string $token, $name=null) : bool{ 
		core::setError(); 
		$name = $name??$this->sessionName; 
		if(!isset($_SESSION[$name])) 
			return core::setError(1, 'No find session token'); 
		if($token == $_SESSION[$name]) 
			return true; 
		return false; 
	}
	public function isXMLHttpRequest() : bool{ 
		core::setError(); 
		return (core::$library->network->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest'); 
	}
};
?>