<?php
return $this->python = new class(){ //create python library
	public $version = '1.0'; //version
	public function run(string $path){
		$command = escapeshellcmd($path);
		$output = shell_exec($command);
		return $output;
	}
}; 
?>