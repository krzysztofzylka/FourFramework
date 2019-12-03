<?php
return $this->string = new class(){ 
	public $version = '1.1'; 
	public function between(string $string, string $start, string $end, int $offset=0) : string{ 
		core::setError(); 
		$strpos1 = core::$library->string->strpos($string, '"', 0+(2*$offset))+1; 
		$strpos2 = core::$library->string->strpos($string, '"', 1+(2*$offset)); 
		return substr($string, $strpos1, $strpos2-$strpos1); 
	}
	public function strpos(string $string, string $searchString, int $offset = 0) : int{
		core::setError(); 
		if($offset < 0) 
			return core::setError(1, 'offset error', 'offset must be greater than -1'); 
		$stringLen = strlen($string); 
		$searchStringLen = strlen($searchString); 
		for($i=0; $i<=$stringLen-1; $i++){ 
			if($string[$i] == $searchString[0]){ 
				if($i+$searchStringLen > $stringLen) 
					continue; 
				$generateString = ''; 
				for($x=0; $x<=$searchStringLen-1; $x++) 
					$generateString .= $string[$i+$x]; 
				if($generateString == $searchString){ 
					if($offset == 0) 
						return $i; 
					else 
						$offset--; 
				}
			}
		}
		return -1; 
	}
	public function generateString(int $length = 15, array $data = [true, true, true, true]) : string{ 
		core::setError(); 
		$return = ''; 
		$string = ''; 
		if($data[0] === true) $string .= '0123456789'; 
		if($data[1] === true) $string .= 'abcdefghijklmnopqrstuvwxyz'; 
		if($data[2] === true) $string .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
		if($data[3] === true) $string .= '!@#%^&*()_+=-}{[]?'; 
		$stringLen = strlen($string); 
		for($i=1; $i<=$length; $i++) 
			$return .= $string[rand(1, $stringLen)-1]; 
		return $return; 
	}
	public function clean(string $string) : string{ 
		core::setError(); 
		$string = strip_tags($string); 
		if(!get_magic_quotes_gpc())
			$string = addslashes($string); 
		return $string; 
	}
};
?>