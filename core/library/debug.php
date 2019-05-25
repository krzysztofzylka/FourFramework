<?php
return $this->debug = new class($this->core){
	protected $core;
	public $version = '1.0';
	public function __construct($obj){
		$this->core = $obj;
	}
	public function print_r($array, bool $var_type=false, string $title='ARRAY'){
		$this->core->returnError();
		if(is_object($array)){
			if(method_exists($array, '__debugInfo'))
				$array = $array->__debugInfo();
			else
				return $this->core->returnError(1, 'this element is not an array');
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
					$this->print_r($v, $var_type);
					echo "</td></tr>";
			}
			echo "</table>";
			return true;
		}
		echo $array;
	}
	public function info() : void{
		$this->core->returnError();
		$curl = curl_version();
		$curl['protocols'] = implode(',', $curl['protocols']);
		$serwer = [
			'PHP' => [
				'Version' => phpversion(),
				'Other' => [
					'CURL' => $curl['version'],
				],
			],
		];
		$this->print_r($serwer, false, 'Serwer');
		echo '<br />';
		$this->print_r($curl, false, 'CURL');
		return;
	}
	public function getOS() : int{
		switch (true) {
            case stristr(PHP_OS, 'DAR'): return 2;
            case stristr(PHP_OS, 'WIN'): return 3;
            case stristr(PHP_OS, 'LINUX'): 4;
            default : return 1;
        }
	}
}
?>