<?php
return $this->string = new class(){ //create class
	public function between(string $string, string $start, string $end, int $offset=0) : string{ //get string between
		core::setError(); //reset error
		$sub = substr($string, strpos($string,$start, $offset)+strlen($start),strlen($string)); //get substr
		return substr($sub,0,strpos($sub,$end)); //return substr
	}
	public function generateString(int $length = 15, array $data = [true, true, true, true]){ //generate string
		core::setError(); //reset error
		$return = ''; //return value
		$string = ''; //string value
		if($data[0] === true) $string .= '0123456789';
		if($data[1] === true) $string .= 'abcdefghijklmnopqrstuvwxyz';
		if($data[2] === true) $string .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if($data[3] === true) $string .= '!@#%^&*()_+=-}{[]?';
		$stringLen = strlen($string); //count string len
		for($i=1; $i<=$length; $i++) //loop
			$return .= $string[rand(1, $stringLen)-1]; //add char
		return $return; //clean generate string
	}
	public function clean(string $string) : string{ //clean string
		core::setError(); //reset error
		if(!get_magic_quotes_gpc())
			$string = addslashes($string); //add slasher
		$string = strip_tags($string); //strip tags
		return $string; //return clean text
	}
};
?>