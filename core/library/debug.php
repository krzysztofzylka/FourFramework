<?php
return $this->debug = new class(){ //create library
	public $version = '1.2'; //version
	public $consoleLog = True; //write to console log
	public function print_r($array, bool $var_type=false, string $title='ARRAY'){ //print_r
		core::setError(); //clear error
		if(is_object($array)){ //if is array
			if(method_exists($array, '__debugInfo')) //if method exists
				$array = $array->__debugInfo(); //debug info
			else //if is not array
				return core::setError(1, 'this element is not an array'); //return error 1
		}
		if(is_array($array)){ //if is array
			echo '<table border=1 cellspacing=0 cellpadding=3 width=100%>';
			echo '<tr><td colspan=2 style="background-color:#333333;"><strong><font color=white>'.$title.'</font></strong></td></tr>';
			foreach ($array as $k => $v) {
					echo '<tr><td valign="top" style="width: 50px; background-color:#F0F0F0;">';
					echo '<strong>' . $k . '</strong> ';
					if($var_type===true){
						if(!is_array($v)){
							$type = gettype($v); //get type
							if($type === 'integer')
								$type = 'int';
							echo '<i style="background: #79ff4c; border-radius: 4px;">{'.$type.'}</i>';
						}
					}
					echo '</td><td>';
					$this->print_r($v, $var_type); //recursive function
					echo "</td></tr>";
			}
			echo "</table>";
			return true;
		}
		echo $array;
	}
	public function getOS() : int{ //get OS
		Switch(true) {
            case stristr(PHP_OS, 'DAR'): return 2; //dar
            case stristr(PHP_OS, 'WIN'): return 3; //win
            case stristr(PHP_OS, 'LINUX'): 4; //linux
            default : return 1;
        }
	}
	public function consoleLog($data, string $title=null) : bool{ //debug to console log
		if($this->consoleLog == false) //if consoleLog is disabled
			return false; //return false
		if(is_array($data)) //if array
			$data = $this->_consoleLogArrayToString($data);
		$data = str_replace(PHP_EOL, '\r\n', $data); //replace enter
		$data = str_replace('	', '', $data); //replace tab
		echo "<script>console.log('Debug Objects: ".($title<>false?$title.'\r\n':'')." ".$data."');</script>"; //write to console log
		return true;
	}
	private function _consoleLogArrayToString(array $data){
		$return = "";
		foreach($data as $name=>$itemData){
			if(is_array($itemData))
				$itemData = $this->_consoleLogArrayToString($itemData);
			$return .= $name.' => ['.$itemData.']'.PHP_EOL;
		}
		return $return;
	}
}
?>