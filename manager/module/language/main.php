<?php 
return new class($this, $config){ 
    private $core;
    private $config;
	private $lang = null;
	public $data = [];
	public $path = '';
	public $returnempty = false;
	public $defaultLang = "pl";
    public function __construct($core, $config){
        $this->core = $core;
        $this->config = $config;
		$this->path = $this->config['path'].'lang/';
    }
	public function loadLang(string $lang = 'default') : bool{
		if($lang == "default")
			$lang = $this->defaultLang;
		$file = $this->path.'lang-'.$lang.'.php';
		if(!file_exists($file))
			return $this->core->returnError(1, 'file not found', $file);
		$this->lang = $lang;
		$this->data = require($file);
		return true;
	}
	public function get(string $name) : string{
		if(empty($this->lang))
			return $this->core->returnError(1, 'lang is not defined');
		return isset($this->data[$name])?$this->data[$name]:($this->returnempty?'':'{lang:'.$name.'}');
	}
} 
?>