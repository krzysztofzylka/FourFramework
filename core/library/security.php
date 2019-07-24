<?php
return $this->security = new class(){ //create security library
	public $sessionName = 'SessProtToken'; //session name
	public $token; //token
	public function createToken() : string{
		core::setError(); //reset error
		$this->token = core::$library->crypt->hash(core::$library->string->generateString(25), 'md5'); //create token
		$_SESSION[$this->sessionName] = $this->token; //add token to session
		return $this->token; //return token
	}
	public function checkToken(string $token) : bool{ //check token
		core::setError(); //reset error
		if(!isset($_SESSION[$this->sessionName])) //if not exists
			return core::setError(1, 'No find session token'); //return error 1
		if($token == $_SESSION[$this->sessionName]) //check token
			return true; //return true
		return false; //return false
	}
	public function isXMLHttpRequest() : bool{ //check XMLHttpRequest
		return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest'); //return data
	}
};
?>