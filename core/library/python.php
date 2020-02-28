<?php
return $this->python = new class(){ 
	public $version = '1.1'; 
	public function run(string $path){
		core::setError();
		if(!file_exists($path))
			return core::setError(1, 'file not found');
		$command = escapeshellcmd($path);
		$output = shell_exec($command);
		return $output;
	}
}; 
?>