<?php
return $this->string = new class(){ //create class
	public $version = '1.1'; //version
	public function between(string $string, string $start, string $end, int $offset=0) : string{ //get string between
		core::setError(); //reset error
		$strpos1 = core::$library->string->strpos($string, '"', 0+(2*$offset))+1; //get first strpos
		$strpos2 = core::$library->string->strpos($string, '"', 1+(2*$offset)); //get second strpos
		return substr($string, $strpos1, $strpos2-$strpos1); //return substr
	}
	//TODO: dodać do specyfikacji
	public function strpos(string $string, string $searchString, int $offset = 0) : int{
		core::setError(); //reset error
		if($offset < 0) //check offset
			return core::setError(1, 'integer error', 'offset must be greater than -1'); //return error
		$stringLen = strlen($string); //get string length
		$searchStringLen = strlen($searchString); //get search string length
		for($i=0; $i<=$stringLen-1; $i++){ //string loop
			if($string[$i] == $searchString[0]){ //if char == first search char
				if($i+$searchStringLen > $stringLen) //if generate length > string length
					continue; //continue
				$generateString = ''; //define generate string
				for($x=0; $x<=$searchStringLen-1; $x++) //generate loop
					$generateString .= $string[$i+$x]; //add char
				if($generateString == $searchString){ //if search
					if($offset == 0) //if search
						return $i; //return char number
					else //search next time
						$offset--; //offset -1
				}
			}
		}
		return -1; //return -1 if error
	}
	public function generateString(int $length = 15, array $data = [true, true, true, true]) : string{ //generate string
		core::setError(); //reset error
		$return = ''; //return value
		$string = ''; //string value
		if($data[0] === true) $string .= '0123456789'; //int
		if($data[1] === true) $string .= 'abcdefghijklmnopqrstuvwxyz'; //str
		if($data[2] === true) $string .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; //big str
		if($data[3] === true) $string .= '!@#%^&*()_+=-}{[]?'; //add
		$stringLen = strlen($string); //count string len
		for($i=1; $i<=$length; $i++) //loop
			$return .= $string[rand(1, $stringLen)-1]; //add char
		return $return; //clean generate string
	}
	public function clean(string $string) : string{ //clean string
		core::setError(); //reset error
		$string = strip_tags($string); //strip tags
		if(!get_magic_quotes_gpc())
			$string = addslashes($string); //add slasher
		return $string; //return clean text
	}
};
?>