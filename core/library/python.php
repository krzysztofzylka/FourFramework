<?php
return $this->python = new class(){ 
	public $version = '1.0'; 
	public function run(string $path){
		core::setError();
		$command = escapeshellcmd($path);
		$output = shell_exec($command);
		return $output;
	}
}; 
?>