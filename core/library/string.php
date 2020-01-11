<?php
return $this->string = new class(){ 
	public $version = '1.2'; 
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
		if($length < 1)
			return core::setError('length error', 'length must be greate than 1');
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
	public function explode($delim, string $string, int $limit = -1){
		$skipChars = ['`', '"', '\''];
		if(is_array($delim))
			$endChars = $delim;
		else
			$endChars = [$delim];
		$return = [];
		$findString = '';
		$skip = false;
		$skipChar = null;
		$skipDeactive = false;
		$count = 0;
		for($i=0; $i<strlen($string); $i++){
			$char = $string[$i];
			if($char === '\\' and $string[$i+1] === $skipChar)
				$skipDeactive = true;
			$prevChar = $i>0?$string[$i-1]:null;
			if((array_search($char, $endChars)===false) and $skip === false){
				$skipSearch = array_search($char, $skipChars);
				if($skipSearch >= -1){
					$skipChar = $skipChars[$skipSearch];
					$skip = true;
				}
				$findString .= $char;
			}elseif($skip === true){
				$findString .= $char;
				if($char === $skipChar){
					if($skipDeactive === false){
						$skipChar = null;
						$skip = false;
					}else
						$skipDeactive = false;
				}
			}else{
				array_push($return, $findString);
				$count++;
				if($count+1 == $limit){
					$lastString = substr($string, $i+1);
					array_push($return, $lastString);
					return $return;
				}
				$findString = '';
			}
		}
		if($count == $limit)
			return $return;
		array_push($return, $findString);
		return $return;
	}
};
?>