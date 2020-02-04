<?php
return $this->string = new class(){ 
	public $version = '1.4a'; 
	public function between(string $string, string $start, string $end, int $offset=0){ 
		core::setError(); 
		$strpos1 = core::$library->string->strpos($string, $start, 0+(2*$offset))+1; 
		$strpos2 = core::$library->string->strpos($string, $end, 0+(2*$offset)+($start==$end?1:0));
		if($strpos1 < 0 or $strpos2 < 0)
			return null;
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
	public function explode(string $delim, string $string, int $limit = -1, array $option = []) : array{
		core::setError();
		if(!isset($option['removeQuotes']))
			$option['removeQuotes'] = false;
		$skipChars = ['`', '"', '\''];
		$return = [];
		$findString = '';
		$skip = false;
		$skipChars = ['`', '"', '\''];
		$skipChar = null;
		$skipDeactive = false;
		for($i=0; $i<strlen($string); $i++){
			$char = $string[$i];
			$charString = '';
			for($x=0; $x<strlen($delim); $x++)
				if(strlen($string) > $i+$x)
					$charString .= $string[$i+$x];
			if($charString == $delim and $skip === false){
				$i = $i+strlen($delim)-1;
				if($findString <> ''){
					array_push($return, $findString);
					$findString = '';
				}
			}else{
				if($char === '\\' and $string[$i+1] === $skipChar)
					$skipDeactive = true;
				if(!(array_search($char, $skipChars)===false) and $skip === false){
					$skipChar = $skipChars[array_search($char, $skipChars)];
					$skip = true;
				}elseif($char === $skipChar and $skip === true){
					if($skipDeactive === true)
						$skipDeactive = false;
					else
						$skip = false;
				}
				$findString.= $char;
			}
		}
		array_push($return, $findString);
		if($option['removeQuotes'])
			foreach($return as $id => $item)
				$return[$id] = $this->removeQuotes($item);
		return $return;
	}
	public function removeQuotes(string $string){
		core::setError();
		if(strlen($string) == 0)
			return $string;
		$list = ['`', '"', '\''];
		$searchFirstInt = array_search($string[0], $list);
		$searchFirst = $searchFirstInt>-1;
		$searchLast = $searchFirst===true?substr($string, strlen($string)-1)==$list[$searchFirstInt]:false;
		if($searchFirst and $searchLast)
			return substr($string, 1, strlen($string)-2);
		return $string;
	}
	public function countString(string $string, string $search){
		core::setError();
		$count = 0;
		while(true){
			$strpos = $this->strpos($string, $search, $count);
			if($strpos == -1)
				break;
			$count++;
		}
		return $count;
	}
};
?>