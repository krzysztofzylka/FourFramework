<?php
return new class($this, $config){
	//zmienna z jądrem
	private $core;
	//zmienna z konfiguracją
	private $config;
	//zmienna z klasą smarty
	public $smarty;
	//główna funkcja
	public function __construct($obj, $config){
		//inicjonowanie zmiennych
		$this->core = $obj;
		$this->config = $config;
		$this->smarty = new Smarty;
		$this->smarty->setTemplateDir($obj->path['dir_template']);
		$this->smarty->setCompileDir($obj->path['dir_temp'].'smarty/');
	}
	//funkcja debugująca
	public function __debugInfo(){
		return [
			//wersja modułu
			'version' => $this->config['version'],
			'smarty' => [
				'version' => '3.1.34-dev',
				'caching' => $this->smarty->caching,
				'template_dir' => $this->smarty->getTemplateDir(),
			],
		];
	}
}
?>