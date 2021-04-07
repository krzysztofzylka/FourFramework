<?php
return $this->string = new class(){ 
	public $version = '1.7';

	public function between(string $string, string $start, string $end, int $offset=0){ 
		core::setError();

		if ($offset < -1) {
			return core::setError(1, 'offset error');
		}

		$return = [];
		$adding = $start===$end?1:0;
		$multiple = $offset===-1?true:false;

		if ($multiple) {
			$offset = 0;
		}

		do {
			$strpos1 = $this->strpos($string, $start, $offset*($adding===1?2:1))+strlen($start); 
			$strpos2 = $this->strpos($string, $end, $adding+(($adding===1?2:1)*$offset));

			if ($strpos1 < 0 or $strpos2 < 0) {
				break;
			}
			
			array_push($return, substr($string, $strpos1, $strpos2-$strpos1));
			
			if (!$multiple) {
				break;
			}

			$offset++;
		} while (true);

		if (!$multiple) {
			return @$return[0];
		}

		return $return;
	}
	public function strpos(string $string, string $searchString, int $offset = 0) : int{
		core::setError();

		if ($offset < 0) {
			return core::setError(1, 'offset error', 'offset must be greater than -1');
		}

		$stringLen = strlen($string);
		$searchStringLen = strlen($searchString);

		for ($i=0; $i<=$stringLen-1; $i++) {
			if (strval($string[$i]) == strval($searchString[0])) {
				if ($i+$searchStringLen > $stringLen) {
					break;
				}

				$generateString = '';

				for ($x=0; $x<=$searchStringLen-1; $x++) {
					$generateString .= $string[$i+$x];
				}

				if ($generateString == $searchString) {
					if($offset == 0){
						return $i;
					}
					
					$offset--;
				}
			}
		}

		return -1;
	}
	public function generateString(int $length = 15, array $data = [true, true, true, true]) : string{ 
		core::setError();

		if ($length <= 0) {
			return core::setError('length error', 'length must be greate than 1');
		}

		$return = '';
		$string = '';

		if ($data[0]) {
			$string .= '0123456789';
		}

		if (isset($data[1]) and $data[1]) {
			$string .= 'abcdefghijklmnopqrstuvwxyz';
		}

		if (isset($data[2]) and $data[2]) {
			$string .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		}

		if (isset($data[3]) and $data[3]) {
			$string .= '!@#%^&*()_+=-}{[]?';
		}

		$stringLen = strlen($string);

		for ($i=1; $i<=$length; $i++) {
			$return .= $string[rand(1, $stringLen)-1];
		}

		return $return;
	}
	public function clean(string $string) : string{ 
		core::setError();

		return addslashes(strip_tags(trim($string)));
	}
	public function removeQuotes(string $string){
		core::setError();

		if (strlen($string) == 0) {
			return $string;
		}

		$list = ['`', '"', '\''];
		$searchFirstInt = array_search($string[0], $list);
		$searchFirst = $searchFirstInt>-1;
		$searchLast = $searchFirst===true?substr($string, strlen($string)-1)==$list[$searchFirstInt]:false;
		
		if ($searchFirst and $searchLast) {
			return substr($string, 1, strlen($string)-2);
		}
		
			return $string;
	}
	public function countString(string $string, string $search){
		core::setError();

		$findCount = 0;
		while (true) {
			if ($this->strpos($string, $search, $findCount) == -1) {
				break;
			}
			$findCount++;
		}
		return $findCount;
	}
	public function convertString(string $name){
		core::setError();

		$name = str_replace('{date}', date('Y-m-d H:i:s'), $name);
		$name = str_replace('{year}', date('Y'), $name);
		$name = str_replace('{month}', date('m'), $name);
		$name = str_replace('{day}', date('d'), $name);
		$name = str_replace('{hour}', date('H'), $name);
		$name = str_replace('{min}', date('i'), $name);
		$name = str_replace('{sec}', date('s'), $name);

		return $name;
	}
};
?>