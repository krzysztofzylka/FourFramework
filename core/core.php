<?php
//main class
class core{
	//framework version
	public $version = '0.1.9 Alpha';
	public $releaseDate = '11.02.2019';
	//private
	private $__lastInsertClass;
	//main function
	public function __construct($config = []){
		//create reversion path
		$this->reversion = "";
		$this->__getReversion();
		//include variable
		require_once('variable.php');
		$this->returnError();
		//create temp dir
		if(!file_exists($this->path['dir_temp']))
			mkdir($this->path['dir_temp']);
		//loading configuration
		foreach($config as $item){
			$key = array_keys($item)[0];
			$value = $item[$key];
			$value = str_replace("'", "\'", $value);
			$script = '$this->'.$key.' = "'.$value.'";';
			eval($script);
		}
		//if the visibility of errors is active
		if($this->error){
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('default_enable', 1);
		}
		//if saving errors to the file is active
		if($this->php_error){
			$dir = $this->path['dir_log_php'].$this->php_error_file;
			ini_set("error_log", $dir);
		}
		//uruchamianie bibliotek
		$this->library = include($this->path['dir_core'].'library.php');
		//uruchamianie rozszerzeń
		$this->_startExtension();
		//pobieranie konfiguracji
		include($this->path['dir_core'].'config.php');
	}
	//loading the view
	public function loadView(string $name, string $dir = "view/"){
		$this->returnError();
		try{
			//generating path to file
			$path = $this->reversion.$dir.basename($name).'.php';
			//if the file does not exist
			if(!is_file($path))
				return $this->returnError(1, 'error loading file', null, 'Error loading view file on path: '.$path, 'core', 'error'); //error 1
			$this->wlog('Success loading view file on path: '.$path, 'core', 'message');
			return require($path);
		}catch (Exception $e){
			return $this->returnError(2, 'error inside function', $e->getMessage(), null, 'Error in the loadView function: '.$e->getMessage(), 'core', 'error'); //error 2
		}
	}
	//loading the module
	public function loadModule(string $name){
		$this->returnError();
		$name = basename($name);
		//generating path to file
		$path = $this->path['dir_module'].$name.'/'; //path to module
		$path_config = $path."config.php"; //path to config
		//if module is already exists
		if($this->checkModule($name))
			return $this->returnError(1, 'module is alerty exists', null, 'module \''.$name.'\' is alerty exists', 'core', 'message'); //error 1
		//if the module does not have a config file
		if(!is_file($path_config))
			return $this->returnError(2, 'error loading module', 'file is not exists', 'error loading module '.$name.' on path: '.$path, 'core', 'error'); //error 2
		//loading configuration to array
		$config = include($path_config);
		$config['path'] = $path;
		if(!isset($config['name']) or $config['name'] == '')
			$config['name'] = $name;
		$this->module_config[$name] = $config;
		//if the module requires other modules
		if(isset($config['module_include']))
			foreach($config['module_include'] as $requiremodule)
				if(!$this->checkModule($requiremodule))
					return $this->returnError(3, 'error loading module, (request other module)', null, 'Error loading module '.$name.', you must include and configurate other module: '.$requiremodule, 'core', 'error'); //error 3
		//loading files from include array
		foreach($config['include'] as $file) require_once($path.$file);
		//loading class from file
		$className = $this->_loadClassFromFile($path.$config['main_file'], $config);
		if($className !== false)
			$this->module[$name] = new $className($this, $config);
		//adding the module name to the module_list array
		array_push($this->module_list, $name);
		$this->wlog('Success loading module '.$name.' on path: '.$path, 'core', 'message');
		//returning the module class
		return $this->module[$name];
	}
	//Removing the loaded module
	public function unloadModule(string $name) : bool{
		$this->returnError();
		//checking if the module is loaded
		if(!$this->checkModule($name))
			return $this->returnError(1, 'module is alerty exists'); //error 1
		//get array id
		$id = array_search($name, $this->model_list);
		//remove module
		unset($this->module_list[$id], $this->module_config[$name], $this->module[$name]);
		return true;
	}
	//checking if the module is loaded
	public function checkModule(string $name) : bool{
		$this->returnError();
		return in_array($name, $this->module_list);
	}
	//loading the model
	public function loadModel(string $name, string $dir = "model/"){
		$this->returnError();
		$name = basename($name);
		//checking if the model is loaded
		if(!in_array($name, $this->model_list)){
			//generate path to file
			$path = $this->reversion.$dir.$name.'.php';
			//if the file does not exist
			if(!is_file($path))
				return $this->returnError(1, 'error loading model file', 'file not exists', 'Error loading model file on path: '.$path, 'core', 'message'); //error 1
			//loading class from file
			$className = $this->_loadClassFromFile($path);
			if($this->library->class->is_anonymous($className)) $this->model[$name] = $this->__lastInsertClass;
			else $this->model[$name] = new $className($this);
			//adding model name to model_list array
			array_push($this->model_list, $name);
			$this->wlog('Success loading model file on path: '.$path, 'core', 'message');
			//return the model class
			return $this->model[$name];
		}else return $this->returnError(2, 'model is alerty exists'); //error 2
	}
	//loading the controller
	public function loadController(string $name, string $dir = "controller/"){
		$this->returnError();
		//generating path to file
		$path = $this->reversion.$dir.basename($name).'.php';
		//if the file does not exist
		if(!is_file($path))
			return $this->returnError(1, 'error loading controller file', 'file is not exists', 'Error loading controller file on path: '.$path, 'core', 'error'); //error 1
		//loading class
		$className = $this->_loadClassFromFile($path);
		if($this->library->class->is_anonymous($className) or $className === false) $object = $this->__lastInsertClass;
		else $object = new $className($this);
		$this->wlog('Success loading controller file on path: '.$path, 'core', 'message');
		return $object;
	}
	//loading the template
	public function Template(string $file, $dir = null, $ext = null) : string{
		$this->returnError();
		$file = basename($file);
		$dir = $dir ?? $this->path['dir_template'];
		$ext = $ext ?? $this->template_extension;
		//generate path to file
		$path = $this->reversion.$dir.$file.$ext;
		//if the file does not exist
		if(!is_file($path))
			return $this->returnError(1, 'error loading template file', 'file not exists', 'Error loading template file on path: '.$path, 'core', 'error'); //error 1
		//load the contents of the file into the variable
		$data = file_get_contents($path);
		//conventering data
		foreach($this->array_template as $text => $content)
			$data = str_replace("{\$".$text."\$}", $content, $data);
		$data = preg_replace('({\$(.*?)\$})', "", $data);
		$this->wlog('Success loading template file on path: '.$path, 'core', 'message');
		return $data;
	}
	//loading data to template
	public function templateSet(string $name, string $value, bool $edit=true) : bool{
		$this->returnError();
		if(in_array($name, $this->array_template_list) and $edit){
			$this->array_template[$name] .= $value;
			return true;
		}else{
			$this->array_template[$name] = $value;
			array_push($this->array_template_list, $name);
			return true;
		}
		return $this->returnError(1, 'error set template'); //error 1
	}
	//debugging function
	public function __debugInfo() : array{
		$this->returnError();
        return [
			'version' => $this->version,
			'reversion' => $this->reversion==''?'***none***':$this->reversion,
			'error' => [
				'show_error' => $this->error==true?'true':'false',
				'php_error' => [
					'active' => $this->php_error==true?'true':'false',
					'file' => $this->php_error_file,
				],
			],
			'module' => [
				'count' => count($this->module_list),
				'list' => empty($this->module_list)==true?'***empty***':$this->module_list,
			],
			'model' => [
				'count' => count($this->model_list),
				'list' => empty($this->model_list)==true?'***empty***':$this->model_list,
			],
			'template' => [
				'extension' => $this->template_extension,
			],
			'log' => [
				'save' => $this->log_save==true?'true':'false',
				'file' => $this->log_file,
				'hidden_type' => $this->log_hide_type,
			],
			'api' => [
				'secure' => $this->API_secure?'true':'false',
				'url' => $this->API,
			],
			'extension' => [
				[
					'name' => 'db',
					'debug' => $this->db->__debugInfo()
				],
				[
					'name' => 'moduleManager',
					'debug' => $this->moduleManager->__debugInfo()
				],
				[
					'name'=>'test',
					'debug'=> $this->test->__debugInfo()
				],
			],
			'library' => [
				'count' => count($this->library->__list),
				'list' => $this->library->__list,
			],
			'path' => $this->path,
		];
    }
	//adding a log
	public function wlog(string $value, $name=null, $type=null) : bool{
		$this->returnError();
		//checking if the addition of logs is active
		if(!$this->log_save)
			return $this->returnError(1, 'error save to log', 'save to file is disabled'); //error 1
		if(in_array($type, $this->log_hide_type))
			return $this->returnError(2, 'error save to log', 'this type is hidden'); //error 2
		//generate string
		$string = '['.date('Y.m.d h:m:s.v').'] ['.$name.'] ['.$type.'] ['.$value.']'.PHP_EOL;
		//path to file
		$path = $this->path['dir_log'].$this->log_file;
		//if the file does not exist
		if(!file_exists($path))
			touch($path);
		//adding data to log
		for($i=0;$i<5;$i++){
			$save = @file_put_contents($path, $string, FILE_APPEND);
			if($save != false)
				break;
		}
		return true;
	}
	//loading the class and returning its name
	private function _loadClassFromFile(string $path, $config=null){
		$this->returnError();
		try{
			$cln = get_declared_classes();
			//loading file
			$includeClass = include($path);
			$this->__lastInsertClass = $includeClass;
			$classname = array_diff(get_declared_classes(), $cln);
			if(count($classname) ==  0)
				return $this->returnError(1, 'error loading class', null, 'error find object in file, path: \''.$path.'\'', 'core', 'error'); //error 1
			//return class name
			$key = $classname[key($classname)];
			return $key;
		}catch (Exception $e){
			return $this->returnError(2, 'other error load class from file', $e->getMessage(), 'error in _loadClassFromFile: '.$e->getMessage(), 'core', 'error'); //error 2
		}
	}
	//start extension
	private function _startExtension() : void{
		$this->returnError();
		//load db extenstion
		$this->db = require_once($this->path['dir_ext_db'].'db.php');
		//load moduleManager extension
		$this->moduleManager = require_once($this->path['dir_ext_moduleManager'].'moduleManager.php');
		//load test extension
		$this->test = require_once($this->path['dir_ext_test'].'test.php');
		return;
	}
	//API
	public function _API($script=''){
		$this->returnError();
		$script = htmlspecialchars($script);
		$dane = [];
		$explode = explode(';', $script);
		foreach($explode as $exp){
			$value = explode('=', $exp, 2);
			array_push($dane, $value);
		}
		$get = '?';
		foreach($dane as $value){
			switch($value[0]){
				case 'search':
					$get .= 'search='.$value[1].'&';
					break;
				case 'uid':
					$get = '?uid='.$value[1];
					break 2;
			};
		}
		if(substr($get, strlen($get)-1) == '&')
			$get = substr($get, 0, strlen($get)-1);
		$url = $this->API.$get;
		return $this->library->network->getJSONData($url);
	}
	//check/show php version
	public function _phpv($version=null){
		$this->returnError();
		if($version == null)
			return PHP_VERSION;
		if(PHP_VERSION >= $version)
			return true;
		return false;
		
	}
	//create reversion path
	private function __getReversion() : void{
		$this->returnError();
		for($i=0; $i<=100; $i++){
			if(file_exists($this->reversion."core/core.php"))
				return;
			$this->reversion .= "../";
		}
		return;
	}
	//return error
	public function returnError($number=-1, $name=null, $message=null, $log_value=null, $log_name=null, $log_type=null) : bool{
		$this->lastError = [
			'number' => $number,
			'name' => $name,
			'message' => $message
		];
		if($log_value <> null) $this->wlog($log_value, $log_name, $log_type);
		return false;
	}
}