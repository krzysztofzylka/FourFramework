<?php
return $this->security = new class(){ //create security library
	public $version = '1.0'; //version
	public $sessionName = 'SessProtToken'; //session name
	public $token; //token
	public function createToken($name=null) : string{ //create token
		core::setError(); //reset error
		$name = $name??$this->sessionName; //session name
		$this->token = core::$library->crypt->hash(core::$library->string->generateString(25), 'md5'); //create token
		$_SESSION[$name] = $this->token; //add token to session
		return $this->token; //return token
	}
	public function checkToken(string $token, $name=null) : bool{ //check token
		core::setError(); //reset error
		$name = $name??$this->sessionName; //session name
		if(!isset($_SESSION[$name])) //if not exists
			return core::setError(1, 'No find session token'); //return error 1
		if($token == $_SESSION[$name]) //check token
			return true; //return true
		return false; //return false
	}
	public function isXMLHttpRequest() : bool{ //check XMLHttpRequest
		return (core::$library->network->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest'); //return data
	}
};
?>