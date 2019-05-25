<?php
return $this->string = new class($this->core){
	protected $core;
	public $version = "1.0";
	public function __construct($obj){
		$this->core = $obj;
	}
	public function between(string $string, string $start, string $end, int $offset=0) : string{
		$this->core->returnError();
		$sub = substr($string, strpos($string,$start, $offset)+strlen($start),strlen($string));
		return substr($sub,0,strpos($sub,$end));
	}
	public function generateString(int $length = 15, array $data = [true, true, true, true]){
		$this->core->returnError();
		$return = '';
		$string = '';
		if($data[0] === true) $string .= '0123456789';
		if($data[1] === true) $string .= 'abcdefghijklmnopqrstuvwxyz';
		if($data[2] === true) $string .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if($data[3] === true) $string .= '!@#%^&*()_+=-}{[]?';
		$stringLen = strlen($string);
		for($i=1; $i<=$length; $i++){
			$char = $string[rand(1, $stringLen)-1];
			$return .= $char;
		}
		return $return;
	}
	public function clean(string $string) : string{
		if(!get_magic_quotes_gpc())
			$string = addslashes($string);
		$string = strip_tags($string);
		return $string;
	}
};
?>