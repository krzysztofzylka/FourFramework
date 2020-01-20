<?php
//by Krzysztof Żyłka
//programista.vxm.pl/fourframework
class core{
	public static $error = [-1, '', ''];
	public static $extended = null;
	public static $info = [
		'version' => '0.2.7a Alpha',
		'releaseDate' => '20.01.2020',
		'frameworkPath' => null
	];
	public static $private = [];
	public static $path = [
		'core' => 'core/',
		'controller' => 'controller/',
		'view' => 'view/',
		'model' => 'model/',
		'module' => 'module/',
		'base' => 'core/base/',
		'temp' => 'core/base/temp/',
		'library' => 'core/library/'
	];
	public static $controller = [];
	public static $model = [];
	public static $module = [];
	public static $module_add = [];
	public static $library = [];
	public static $debug = [
		'showError' => True,
		'saveError' => True,
		'showCoreError' => True
	];
	public static function init(){
		self::setError();
		//generate fourframework path
		$frameworkPath = __DIR__.'/';
		$frameworkPath = substr($frameworkPath, 0, strlen($frameworkPath)-strlen('core/'));
		self::$info['frameworkPath'] = $frameworkPath;
		//error
		if(self::$debug['showError'])
			error_reporting(E_ALL);
		foreach(self::$path as $name => $value){
			self::$path[$name] = self::$info['frameworkPath'].$value;
			if(!file_exists(self::$path[$name]))
				mkdir(self::$path[$name], 0700, true);
		}
		//debug
		if(self::$debug['saveError']){
			ini_set("log_errors", 1);
			$path = self::$path['base'].'log/';
			if(!file_exists($path))
				mkdir($path, 0700, true);
			$path .= 'php_error.log';
			ini_set("error_log", $path);
		}
		//include library class
		self::$library = include('library.php');
		return true;
	}
	public static function setError(int $number=-1, string $name='', $description=''){
		self::$extended = null;
		self::$error = [$number, $name, $description];
		if(self::$debug['showCoreError'] == true and $number > -1){
			echo '<b>Core error:</b> ('.$number.') [<i>'.$name.'</i>] ';
			if(is_array($description))
				print_r($description);
			else echo $description.'<br />';
		}
		return false;
	}
	public static function loadView(string $name){
		self::setError();
		$name = htmlspecialchars(basename($name));
		$path = self::$path['view'].$name.'.php';
		if(!file_exists($path))
			return self::setError(1, 'file not exists', 'file not exists in path: ('.$path.')');
		return include($path);
	}
	public static function loadController(string $name){
		self::setError();
		$name = htmlspecialchars(basename($name));
		if(in_array($name, array_keys(self::$controller)))
			return self::setError(3, 'the class has already been loaded', '');
		$path = self::$path['controller'].$name.'.php';
		if(!file_exists($path))
			return self::setError(1, 'file not exists', 'file not exists in path: ('.$path.')');
		$includeClass = include($path);
		if(!is_object($includeClass))
			return self::setError(2, 'the class is incorrect', 'the class does not return an object');
		self::$controller[$name] = $includeClass;
		return $includeClass;
	}
	public static function loadModel(string $name){
		self::setError();
		$name = htmlspecialchars(basename($name));
		if(in_array($name, array_keys(self::$model)))
			return self::setError(3, 'the class has already been loaded', '');
		$path = self::$path['model'].$name.'.php';
		if(!file_exists($path))
			return self::setError(1, 'file not exists', 'file not exists in path: ('.$path.')');
		$includeClass = include($path);
		if(!is_object($includeClass))
			return self::setError(2, 'the class is incorrect', 'the class does not return an object');
		self::$model[$name] = $includeClass;
		return $includeClass;
	}
	public static function loadModule(string $name){
		self::setError();
		$name = htmlspecialchars(basename($name));
		if(in_array($name, array_keys(self::$module)))
			return self::setError(1, 'the class has already been loaded', '');
		$path = self::$path['module'].$name.'/';
		if(!file_exists($path.'config.php'))
			return self::setError(2, 'config file config.php not found', '');
		$config = include($path.'config.php');
		$config['name'] = $name;
		$config['path'] = $path;
		self::$module_add[$config['name']]['config'] = $config;
		if(!is_array($config))
			return self::setError(3, 'config file config.php error', 'the data returned is not a table');
		if(isset($config['include']) and is_array($config['include']))
			foreach($config['include'] as $name)
				include($path.$name);
		if(isset($config['moduleFile']) and !file_exists($path.$config['moduleFile']))
			return self::setError(4, 'module file not found', $path.$config['moduleFile']);
		else
			self::$module[$config['name']] = include($path.$config['moduleFile']);
		return self::$module[$config['name']];
	}
	public static function debug(bool $show = false) : array{
		self::setError();
		$return = [
			'core' => [
				'info' => self::$info,
				'path' => self::$path,
				'debug' => self::$debug
			],
			'library' => is_object(self::$library)?self::$library->__list:false,
			'module' => [
				'list' => implode(', ', array_keys(self::$module)),
				'config' => self::$module_add,
			],
		];
		if($show)
			if(is_object(self::$library))
				self::$library->debug->print_r($return);
			else
				print_r($return);
		return $return;
	}
}
?>