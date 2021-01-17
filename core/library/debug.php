<?php
return $this->debug = new class(){ 
	public $version = '1.4'; 
	public $consoleLog = True; 
	private $memoryLastUsage = 0;
	public function print_r($array, bool $var_type=false, string $title='ARRAY'){
		core::setError(); 
		if(is_object($array)){ 
			if(method_exists($array, '__debugInfo')) 
				$array = $array->__debugInfo(); 
			else 
				$array = (array) $array;
		}
		if(is_array($array)){ 
			echo '<table border=1 cellspacing=0 cellpadding=3 width=100%>';
			echo '<tr><td colspan=2 style="background-color:#333333;"><strong><font color=white>'.$title.'</font></strong></td></tr>';
			foreach ($array as $k => $v) {
					echo '<tr><td valign="top" style="width: 50px; background-color:#F0F0F0;">';
					echo '<strong>' . $k . '</strong> ';
					if($var_type===true){
						if(!is_array($v)){
							$type = gettype($v); 
							if($type === 'integer')
								$type = 'int';
							echo '<i style="background: #79ff4c; border-radius: 4px;">{'.$type.'}</i>';
						}
					}
					echo '</td><td>';
					$this->print_r($v===NULL?'NULL':($v===true?'true':($v===false?'false':$v)), $var_type); 
					echo "</td></tr>";
			}
			echo "</table>";
			return true;
		}
		echo $array;
	}
	public function getOS() : int{ 
		core::setError();
		Switch(true){
            case stristr(PHP_OS, 'DAR'): return 2; 
            case stristr(PHP_OS, 'WIN'): return 3; 
            case stristr(PHP_OS, 'LINUX'): 4; 
            default : return 1;
        }
	}
	public function consoleLog($data, string $title=null) : bool{ 
		core::setError();
		if($this->consoleLog == false) 
			return false; 
		if(is_array($data)) 
			$data = $this->_consoleLogArrayToString($data);
		$data = str_replace(PHP_EOL, '\r\n', $data); 
		$data = str_replace('	', '', $data); 
		echo "<script>console.log('Debug Objects: ".($title<>false?$title.'\r\n':'')." ".$data."');</script>"; 
		return true;
	}
	public function memoryUsage(bool $renew = true, bool $convert = false){
		core::setError();
		$memusage = memory_get_usage();
		$return = $renew===false?($memusage-$this->memoryLastUsage):$memusage;
		$this->memoryLastUsage = $memusage;
		if($convert === true)
			return core::$library->memory->formatBytes($return);
		return $return;
	}
	private function _consoleLogArrayToString(array $data){
		core::setError();
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