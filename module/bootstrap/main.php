<?php
return new class($this, $config){
	private $core;
	private $config;
	public $adding;
	public function __construct($obj, $config){
		$this->core = $obj;
		$this->config = $config;
		$path  = $config['path'].'script/';
		$this->adding = '<link rel="stylesheet" href="'.$path.'style/default.min.css">
		<script src="'.$path.'jquery.min.js"></script>
		<script src="'.$path.'pooper.min.js"></script>
		<script src="'.$path.'bootstrap.min.js"></script>';
	}
	public function setStyle($name='default'){
		$path  = $this->config['path'].'script/';
		$this->adding = '<link rel="stylesheet" href="'.$path.'style/'.$name.'.min.css">
		<script src="'.$path.'jquery.min.js"></script>
		<script src="'.$path.'pooper.min.js"></script>
		<script src="'.$path.'bootstrap.min.js"></script>';
	}
	public function __debugInfo(){
		return [
			'version' => $this->config['version'],
			'bootstrap' => [
				'version' => 'v4.2.1'
			],
		];
	}
}
?>