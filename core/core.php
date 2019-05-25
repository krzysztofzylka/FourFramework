<?php
class core{
	public $version = '0.1.10 Alpha';
	public $releaseDate = '25.05.2019';
	private $__lastInsertClass;
	public function __construct($config = null){
		$this->reversion = "";
		$this->__getReversion();
		require_once('variable.php');
		$this->returnError();
		$this->__createPathDir();
		if(is_array($config)){
			foreach($config as $key => $value){
				$script = '$this->'.$key.' = "'.$value.'";';
				eval($script);
			}
		}
		if($this->error){
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
			ini_set('default_enable', 1);
		}
		if($this->php_error){
			$dir = $this->path['dir_log_php'].$this->php_error_file;
			ini_set("error_log", $dir);
		}
		$this->library = new class($this){
			protected $core;
			public $__list = [];
			public function __construct($obj){
				$this->core = $obj;
			}
			public function __get($name){
				$path = $this->core->path['dir_library'].$name.'.php';
				if(is_file($path)){
					array_push($this->__list, $name);
					return include_once($path);
				}else{
					$this->core->wlog('Error send function to \''.$name.'\' library', 'core', 'error');
					die('<b>Error send function to \''.$name.'\' library</b>');
				}
			}
			public function __debugInfo() : array{
				return [
					'list' => $this->__list,
				];
			}
		};
		if(!file_exists($this->path['dir_base']))
			mkdir($this->path['dir_base']);
	}
	public function loadView(string $name, string $dir = "view/"){
		$this->returnError();
		try{
			$path = $this->reversion.$dir.basename($name).'.php';
			if(!is_file($path))
				return $this->returnError(1, 'error loading file', null, 'Error loading view file on path: '.$path, 'core', 'error');
			$this->wlog('Success loading view file on path: '.$path, 'core', 'message');
			return require($path);
		}catch (Exception $e){
			return $this->returnError(2, 'error inside function', $e->getMessage(), 'Error in the loadView function: '.$e->getMessage(), 'core', 'error'); 
		}
	}
	public function loadModule($name){
		$this->returnError();
		if(is_array($name)){
			foreach($name as $item)
				$this->loadModule($item);
			return;
		}
		$name = basename($name);
		$path = $this->path['dir_module'].$name.'/';
		$path_config = $path."config.php";
		if($this->checkModule($name))
			return $this->returnError(1, 'module is alerty exists', null, 'module \''.$name.'\' is alerty exists', 'core', 'message');
		if(!is_file($path_config))
			return $this->returnError(2, 'error loading module', 'file is not exists', 'error loading module '.$name.' on path: '.$path, 'core', 'error');
		$config = include($path_config);
		if(!is_array($config))
			return $this->returnError(4, 'error read config file', '', 'error loading config file in module '.$name, 'core', 'error');
		$config['path'] = $path;
		if(!isset($config['name']) or $config['name'] == '')
			$config['name'] = $name;
		$this->module_config[$name] = $config;
		if(isset($config['module_include']))
			foreach($config['module_include'] as $requiremodule)
				if(!$this->checkModule($requiremodule))
					return $this->returnError(3, 'error loading module, (request other module)', null, 'Error loading module '.$name.', you must include and configurate other module: '.$requiremodule, 'core', 'error');
		if(isset($config['include']))
			foreach($config['include'] as $file) require_once($path.$file);
		if(!isset($config['main_file']))
			return $this->returnError(5, 'no find main_file in config', null, 'no find main_file in config (module: '.$name.')', 'core', 'error');
		$className = $this->_loadClassFromFile($path.$config['main_file'], $config);
		if($this->library->class->is_anonymous($className))
			$this->module[$name] = $this->__lastInsertClass;
		else
			$this->module[$name] = new $className($this, $config);
		array_push($this->module_list, $name);
		$this->wlog('Success loading module '.$name.' on path: '.$path, 'core', 'message');
		return $this->module[$name];
	}
	public function unloadModule(string $name) : bool{
		$this->returnError();
		if(!$this->checkModule($name))
			return $this->returnError(1, 'module is not exists');
		$id = array_search($name, $this->model_list);
		unset($this->module_list[$id], $this->module_config[$name], $this->module[$name]);
		return true;
	}
	public function checkModule(string $name) : bool{
		$this->returnError();
		return in_array($name, $this->module_list);
	}
	public function loadModel(string $name, $dir = null){
		$this->returnError();
		$dir = $dir ?? $this->path['dir_model'];
		$name = basename($name);
		if(!in_array($name, $this->model_list)){
			$path = $this->reversion.$dir.$name.'.php';
			if(!is_file($path))
				return $this->returnError(1, 'error loading model file', 'file not exists', 'Error loading model file on path: '.$path, 'core', 'message'); 
			$className = $this->_loadClassFromFile($path);
			if($this->library->class->is_anonymous($className))
				$this->model[$name] = $this->__lastInsertClass;
			else
				$this->model[$name] = new $className($this);
			array_push($this->model_list, $name);
			$this->wlog('Success loading model file on path: '.$path, 'core', 'message');
			return $this->model[$name];
		}else return $this->returnError(2, 'model is alerty exists');
	}
	public function loadController(string $name, string $dir = "controller/"){
		$this->returnError();
		$path = $this->reversion.$dir.basename($name).'.php';
		if(!is_file($path))
			return $this->returnError(1, 'error loading controller file', 'file is not exists', 'Error loading controller file on path: '.$path, 'core', 'error');
		$className = $this->_loadClassFromFile($path);
		if($this->library->class->is_anonymous($className) or $className === false) $object = $this->__lastInsertClass;
		else $object = new $className($this);
		$this->wlog('Success loading controller file on path: '.$path, 'core', 'message');
		return $object;
	}
	public function Template(string $file, $dir = null, $ext = null) : string{
		$this->returnError();
		$file = basename($file);
		$dir = $dir ?? $this->path['dir_template'];
		$ext = $ext ?? $this->template_extension;
		$path = $this->reversion.$dir.$file.$ext;
		if(!is_file($path))
			return $this->returnError(1, 'error loading template file', 'file not exists', 'Error loading template file on path: '.$path, 'core', 'error');
		$data = file_get_contents($path);
		foreach($this->array_template as $text => $content)
			$data = str_replace("{\$".$text."\$}", $content, $data);
		$data = preg_replace('({\$(.*?)\$})', "", $data);
		$this->wlog('Success loading template file on path: '.$path, 'core', 'message');
		return $data;
	}
	public function templateSet(string $name, string $value, bool $edit=true) : bool{
		$this->returnError();
		if(in_array($name, $this->array_template_list) and $edit)
			$this->array_template[$name] .= $value;
		else{
			$this->array_template[$name] = $value;
			array_push($this->array_template_list, $name);
		}
		return true;
	}
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
			'library' => [
				'count' => count($this->library->__list),
				'list' => $this->library->__list,
			],
			'path' => $this->path,
			'crypt' => $this->crypt?'true':'false',
		];
    }
	public function wlog(string $value, $name=null, $type=null) : bool{
		$this->returnError();
		if(!$this->log_save)
			return false;
		if(in_array($type, $this->log_hide_type))
			return false;
		$string = '['.date('Y.m.d h:m:s.v').'] ['.$name.'] ['.$type.'] ['.$value.']'.PHP_EOL;
		$path = $this->path['dir_log'].$this->log_file;
		if(!file_exists($path))
			touch($path);
		for($i=0;$i<5;$i++){
			$save = @file_put_contents($path, $string, FILE_APPEND);
			if($save != false)
				break;
		}
		return true;
	}
	private function _loadClassFromFile(string $path, $config=null){
		$this->returnError();
		$cln = get_declared_classes();
		if(!is_readable($path))
			return $this->returnError(2, 'error loading class', null, 'file is not exists or is not readable, path: \''.$path.'\'', 'core', 'error');
		$includeClass = require($path);
		$this->__lastInsertClass = $includeClass;
		$classname = array_diff(get_declared_classes(), $cln);
		if(count($classname) ==  0)
			return $this->returnError(1, 'error loading class', null, 'error find object in file, path: \''.$path.'\'', 'core', 'error');
		$key = $classname[key($classname)];
		return $key;
	}
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
	public function _phpv($version=null){
		$this->returnError();
		if($version == null)
			return PHP_VERSION;
		if(PHP_VERSION >= $version)
			return true;
		return false;
		
	}
	private function __getReversion() : void{
		$this->returnError();
		for($i=0; $i<=100; $i++){
			if(file_exists($this->reversion."core/core.php"))
				return;
			$this->reversion .= "../";
		}
		return;
	}
	public function returnError(int $number=-1, $name=null, $message=null, $log_value=null, $log_name=null, $log_type=null) : bool{
		if($log_value <> null)
			$this->wlog($log_value, $log_name, $log_type);
		$this->lastError = [
			'number' => $number,
			'name' => $name,
			'message' => $message
		];
		if($number > -1 and $this->showError['show'] == true){
			echo '<div id="coreShowError" style="border: 1px solid black; padding: 2px;"><b>Error script</b>';
			if($this->showError['show_number'] == true) echo '<br />Number: '.$number;
			if($this->showError['show_name'] == true) echo '<br />Name: '.$name;
			if($this->showError['show_message'] == true) echo '<br />Message: '.$message;
			echo '</div>';
		}
		return false;
	}
	private function __createPathDir() : void{
		$this->returnError();
		foreach($this->path as $name => $path){
			$isDir = substr($name, 0, 4)=='dir_'?true:false;
			if($isDir and !file_exists($path))
				mkdir($path, 0600, true);
		}
		return;
	}
	public function _clearFramework() : void{
		$this->returnError();
		$path = $this->path;
		$this->library->file->deldir($path['dir_log']);
		$this->library->file->deldir($path['dir_temp']);
		$this->library->file->deldir($path['dir_base']);
		$this->wlog('success clear framework', 'core', 'message');
		return;
	}
	public function libraryExists(string $name) : bool{
		$path = $this->path['dir_library'].$name.'.php';
		return file_exists($path);
	}
	public function _autoConfig(){
		//core path
		foreach($this->path as $name => $value){
			$check = $this->__autoConfigDB('core_path_'.$name);
			if($check <> false)
				$this->path[$name] = $this->reversion.$check;
		}
		//library database
		if((bool)$this->__autoConfigDB('lib_database_autostart') == true){
			$this->library->database;
			if((bool)$this->__autoConfigDB('lib_database_advlog') == true)
				$this->library->database->advanced_logs = true;
			if((bool)$this->__autoConfigDB('lib_database_autoconnect') == true){
				$config = [
					'type' => $this->__autoConfigDB('lib_database_connect_type'),
					'host' => $this->__autoConfigDB('lib_database_connect_host'),
					'name' => $this->__autoConfigDB('lib_database_connect_name'),
					'login' => $this->__autoConfigDB('lib_database_connect_login'),
					'password' => $this->__autoConfigDB('lib_database_connect_password'),
					'sqlite' => $this->__autoConfigDB('lib_database_connect_sqlite'),
					'port' => $this->__autoConfigDB('lib_database_connect_port'),
					'charset' => 'utf8'
				];
				$this->library->database->connect($config);
			}
		}
		//library crypt
		if((bool)$this->__autoConfigDB('lib_crypt_autostart') == true){
			$this->library->crypt;
			$this->library->crypt->salt = $this->__autoConfigDB('lib_crypt_salt');
		}
		//library network
		if((bool)$this->__autoConfigDB('lib_network_autostart') == true){
			$this->library->network;
			$this->library->network->curlTimeout = (int)$this->__autoConfigDB('lib_network_curltimeout');
			if((bool)$this->__autoConfigDB('lib_network_methodManual') == true)
				$this->library->network->method = (int)$this->__autoConfigDB('lib_network_method');
		}
		//autostart module
		$list = $this->__autoConfigDB('module_autostart');
		if($list !== false){
			$list = explode('|', $list);
			$list = array_diff($list, ['']);
			foreach($list as $name){
				$path = $this->path['dir_module'].$name.'/config.php';
				if(file_exists($path))
					$this->loadModule($name);
			}
		}
	}
	public function __autoConfigDB(string $name, $value=null){
		if($value === null){
			$query = $this->library->db->getData('core_autoconfig', ['name='.$name], false);
			if($query == false)
				return false;
			else
				return $query['value'];
		}else{
			$query = $this->library->db->getData('core_autoconfig', ['name='.$name], false);
			if($query == false)
				$this->library->db->addData('core_autoconfig', ['name' => $name, 'value' => $value]);
			else
				$this->library->db->updateData('core_autoconfig', ['name='.$name], ['value='.$value]);
			return true;
		}
	}
}