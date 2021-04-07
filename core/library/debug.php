<?php
return $this->debug = new class(){
	public $version = '1.5';
	public $consoleLog = True;
	private $memoryLastUsage = 0;
	
	public function print_r($array, bool $var_type=false, string $title='ARRAY'){
		core::setError();

		if(is_object($array)){
			if (method_exists($array, '__debugInfo')) {
				$array = $array->__debugInfo(); 
			} else {
				$array = (array) $array;
			}
		}

		if (is_array($array)) { 
			echo '<table border=1 cellspacing=0 cellpadding=3 width=100%>';
			echo '<tr><td colspan=2 style="background-color:#333333;"><strong><font color=white>'.$title.'</font></strong></td></tr>';
			
			foreach ($array as $key => $value) {
					echo '<tr><td valign="top" style="width: 50px; background-color:#F0F0F0;">';
					echo '<strong>' . $key . '</strong> ';

					if ($var_type===true) {
						if (!is_array($value)) {
							$type = gettype($value);
							
							if ($type === 'integer') {
								$type = 'int';
							}

							echo '<i style="background: #79ff4c; border-radius: 4px;">{'.$type.'}</i>';
						}
					}

					echo '</td><td>';
					$this->print_r($value===NULL?'NULL':($value===true?'true':($value===false?'false':$value)), $var_type); 
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
            case stristr(PHP_OS, 'DAR'):
				return 2;
				break;
            case stristr(PHP_OS, 'WIN'):
				return 3;
				break;
            case stristr(PHP_OS, 'LINUX'):
				return 4;
				break;
        }

		return 1;
	}
	public function consoleLog($data, string $title=null) : bool{ 
		core::setError();

		if (!$this->consoleLog) {
			return false;
		}

		if (is_array($data)) {
			echo "<script>console.info('Debug Objects:".($title?' '.$title.'\r\n':'')."');</script>";
			$data = "JSON.parse('".json_encode($data)."')";
		} else {
			$data = "'Debug Objects:".($title?' '.$title.'\r\n':'')." ".$data."'";
		}

		echo "<script>console.log(".$data.");</script>";

		return true;
	}
	public function memoryUsage(bool $renew = true, bool $convert = false){
		core::setError();

		$memusage = memory_get_usage();
		$return = $renew===false?($memusage-$this->memoryLastUsage):$memusage;
		$this->memoryLastUsage = $memusage;

		if ($convert) {
			return core::$library->memory->formatBytes($return);
		}

		return $return;
	}
}
?>