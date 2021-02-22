<?php
return new class(){ //create class
	public $smarty; //smarty main class
	public $templateDir; //template dir
	public function __construct(){ //construct
		$config = core::$module_add['smarty']['config']; //load config to variable
		include($config['path'].'smarty/Smarty.class.php'); //include smarty class
		$this->smarty = new Smarty; //download class
		$this->smarty->caching = true; //caching
		$this->smarty->cache_lifetime = 5; //cache lifetime
		$temp = core::$path['temp'].'smarty/'; //temp dir
		$path = ['cache' => $temp.'cache/','compile' => $temp.'templates_c/']; //path list
		$this->templateDir = core::$info['frameworkPath'].'template/'; //set templateDir
		if(!file_exists($this->templateDir)) //if not exists
			mkdir($this->templateDir, 0777, true); //create template dir
		$protectedFile = $this->templateDir.'.htaccess'; //htaccess path
		if(!file_exists($protectedFile)) //if not exists
			 copy($config['path'].'.htaccess', $protectedFile); //copy .htaccess file
		foreach($path as $dirPath) //path loop
			if(!file_exists($dirPath)) //if not file exists
				mkdir($dirPath, 0777, true); //create dir
		$this->smarty->setTemplateDir($this->templateDir) //set template dir
			->setCompileDir($path['compile']) //set compile dir
			->setCacheDir($path['cache']); //set cache dir
	}
	public function setTemplateDir(string $path){ //set template Dir
		$this->smarty->setTemplateDir($path); //set template dir in smarty
	}
	public function setCaching($data=5){
		if($data === false){
			$this->smarty->caching = false;
			return true;
		}
		$this->smarty->cache_lifetime = (int)$data;
			return true;
	}
}
?>