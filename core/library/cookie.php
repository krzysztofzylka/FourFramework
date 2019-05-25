<?php
return $this->cookie = new class($this->core){
	protected $core;
	public $version = '1.0';
	public $protect = false;
	private $pass = '';
	public function __construct($obj){
		$this->protect = $obj->crypt;
		$this->core = $obj;
		$path = $obj->path['dir_base'].'cookie_protect.txt';
		if(!file_exists($path)){
			$pass = $obj->library->string->generateString(20);
			file_put_contents($path, $pass);
			$this->pass = $pass;
		}else{
			$this->pass = file_get_contents($path);
		}
	}
	public function getCookie(string $name){
		if(!isset($_COOKIE[$name]))
			return $this->core->returnError(1, 'no found');
		$cookie = $_COOKIE[$name];
		if($protect)
			$cookie = $this->core->library->crypt->decrypt($cookie, $this->pass);
		return $cookie;
	}
	public function createCookie(string $name, string $value, int $hour=24) : bool{
		if($this->protect)
			$value = $this->core->library->crypt->crypt($value, $this->pass);
		return $this->setCookie($name, $value, time()+(3600*$hour), "/");
	}
	public function setCookie(string $name, string $value = "", int $expires = 0, string $path = "", string $domain = "", bool $secure = false, $httponly = false) : bool{
		return setCookie($name, $value, $expires, $path, $domain, $secure, $httponly);
	}
	public function deleteCookie(string $name){
		$this->setCookie($name, "", time()-3600);
		if(isset($_COOKIE[$name]))
			unset($_COOKIE[$name]);
	}
	public function createInfo(){
		echo '<div id="_cookieLibrary" style="border-top: 1px solid white; background: #CCCCCC; width: 100%; height: 50px; position: absolute; left: 0px; bottom: 0px;"><p style="word-wrap: break-word;">Nasza strona internetowa używa plików cookies (tzw. ciasteczka) w celach statystycznych, reklamowych oraz funkcjonalnych. Dzięki nim możemy indywidualnie dostosować stronę do twoich potrzeb. Każdy może zaakceptować pliki cookies albo ma możliwość wyłączenia ich w przeglądarce, dzięki czemu nie będą zbierane żadne informacje.</p></div>';
	}
};
?>