<?php
//by Krzysztof Żyłka
//programista.vxm.pl/fourframework
class core{
	public static $isError = false;
	public static $error = [-1, '', '', null]; //0 - numer, 1-nazwa, 2-opis, 3-wywołanie funkcji debug_backtrace
	public static $info = [
		'version' => '0.3.4 Alpha',
		'releaseDate' => '30.03.2021',
		'frameworkPath' => null,
		'reversion' => ''
	];
	public static $path = [
		'core' => 'core/',
		'controller' => 'controller/',
		'view' => 'view/',
		'model' => 'model/',
		'module' => 'module/',
		'base' => 'core/base/',
		'temp' => 'core/base/temp/',
		'library' => 'core/library/',
		'library_api' => 'core/library/API/',
		'log' => 'core/base/log/'
	];
	public static $controller = [];
	public static $model = [];
	public static $module = [];
	public static $module_add = [];
	public static $library = [];
	public static $initialize = false;
	public static $option = [
		'autoCreatePath' => true,
		'multipleModule' => false,
		'enableLocalhostHTTPS' => false,
		'moveToHttps' => false,
		'localPath' => false,
		'localIgnored' => ['library', 'library_api', 'module', 'core'],
		'localPathReversion' => '',
		'protectViewName' => true,
		'protectControllerName' => true,
		'protectModelName' => true,
		'showError' => true,
		'saveError' => true,
		'showCoreError' => true,
		'saveCoreError' => true,
	];
	
	public static function init(array $option = []){
		self::setError();
		self::$initialize = true;
		
		//loading option
		foreach($option as $name => $value)
			if(isset(self::$option[$name]))
				self::$option[$name] = $option[$name];
		
		//displaying php errors
		if(self::$option['showError']) error_reporting(E_ALL);

		//https protect
		if(isset($option['moveToHttps']) and boolval($option['moveToHttps']) == true)
			self::__protectHTTPS($option['enableLocalhostHTTPS']);

		//generating reversion path
		$debugBacktrace = debug_backtrace();
		self::$info['reversion'] = self::__createReversion(pathinfo($debugBacktrace[count($debugBacktrace)-1]['file'])['dirname'], __DIR__);
		
		//generating the path to the core
		self::$info['frameworkPath'] = dirname(__DIR__).DIRECTORY_SEPARATOR;
		
		//generating dir
		foreach(self::$path as $name => $value){
			self::$path[$name] = ((self::$option['localPath']===true and array_search($name, self::$option['localIgnored'])===false)?self::$option['localPathReversion']:self::$info['reversion']).$value; //tworznie ścieżki dla zmiennej $path
			self::$path[$name] = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, self::$path[$name]);
			if(!is_dir(self::$path[$name]) and self::$option['autoCreatePath'] === true)
				mkdir(self::$path[$name], 0700, true);
		}
		
		//debug
		if(self::$option['saveError']){
			ini_set("log_errors", 1);
			ini_set("error_log", self::$path['log'].'php_error_'.date('Ym').'.log');
		}
		
		//include library class
		self::$library = include('library.php');
		return true;
	}
	public static function setError(int $number=-1, string $name='', $description=''){
		self::$isError = $number<>-1?true:false;
		self::$error = [$number, $name, $description, $number>-1?debug_backtrace():null];
		if(self::$option['saveCoreError'] === true and $number > -1){
			$path = self::$path['log'].'core_error_'.date('Ymd').'.log';
			$date = date('Y_m_d h:m:s');
			if(is_array($description))
				$description = json_encode($description);
			$data = '['.$date.'] ['.$number.'] ['.htmlspecialchars($name).'] ['.htmlspecialchars($description).'] ['.base64_encode(json_encode(self::$error[3])).']'.PHP_EOL;
			file_put_contents($path, $data, FILE_APPEND);
		}
		if(self::$option['showCoreError'] === true and $number > -1){
			echo '<b>Core error:</b> ('.$number.') [<i>'.$name.'</i>] ';
			if(is_array($description))
				print_r($description);
			else echo $description.'<br />';
		}
		return false;
	}
	public static function loadView(string $name){
		self::setError();
		if(self::$option['protectViewName']) $name = htmlspecialchars(basename($name));
		$path = self::$path['view'].str_replace('.', DIRECTORY_SEPARATOR, $name).'.php';
		if(!file_exists($path))
			return self::setError(1, 'file not exists', 'file not exists in path: ('.$path.')');
		return include($path);
	}
	public static function loadController(string $name){
		self::setError();
		if(self::$option['protectControllerName']) $name = htmlspecialchars(basename($name));
		if(in_array($name, array_keys(self::$controller)))
			return self::setError(3, 'the class has already been loaded', '');
		$path = self::$path['controller'].str_replace('.', DIRECTORY_SEPARATOR, $name).'.php';
		if(!file_exists($path))
			return self::setError(1, 'file not exists', 'file not exists in path: ('.$path.')');
		$includeClass = include($path);
		if(!is_object($includeClass))
			return self::setError(2, 'the class is incorrect', 'the class does not return an object');
		self::$controller[$name] = $includeClass;
		return $includeClass;
	}
	public static function loadModel($name){
		self::setError();
		if(is_array($name)){
			foreach($name as $modelName)
				self::loadModel($modelName);
			return;
		}
		if(self::$option['protectModelName']) $name = htmlspecialchars(basename($name));
		if(in_array($name, array_keys(self::$model)))
			return self::$model[$name];
		$path = self::$path['model'].str_replace('.', DIRECTORY_SEPARATOR, $name).'.php';
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
		if(in_array($name, array_keys(self::$module)) and self::$option['multipleModule'] === false)
			return self::setError(1, 'The class has already been loaded', '');
		$path = self::$path['module'].$name.DIRECTORY_SEPARATOR;
		if(!file_exists($path.'config.php'))
			return self::setError(2, 'Config file not found', '');
		$config = include($path.'config.php');
		if(!is_array($config))
			return self::setError(3, 'Config file error', 'The data returned is not a table');
		$config = array_merge($config, [
			'name' => $name,
			'path' => $path
		]);
		$moduleArrayName = $name;
		if(in_array($name, array_keys(self::$module)) and self::$option['multipleModule'] === true){
			$i = 2;
			while(true){
				$moduleArrayName = $name.'_k'.$i;
				if(!isset(self::$module[$moduleArrayName]))
					break;
				$i++;
			}
		}
		self::$module_add[$moduleArrayName]['config'] = $config;
		if(isset($config['include']) and is_array($config['include']))
			foreach($config['include'] as $name)
				include($path.$name);
		if(isset($config['moduleFile']) and !file_exists($path.$config['moduleFile']))
			return self::setError(4, 'module file not found', $path.$config['moduleFile']);
		$GLOBALS['module'] = $name;
		$GLOBALS['module_config'] = $config;
		$moduleClass = (self::$module[$moduleArrayName] = include($path.$config['moduleFile']));
		unset($GLOBALS['module_config'], $GLOBALS['module']);
		return $moduleClass;
	}
	public static function debug(bool $show = false) : array{
		self::setError();
		$return = [
			'core' => [
				'info' => self::$info,
				'path' => self::$path,
				'option' => self::$option
			],
			'library' => is_object(self::$library)?self::$library->__list:false,
			'module' => [
				'list' => implode(', ', array_keys(self::$module)),
				'config' => self::$module_add,
			],
			'model' => array_keys(self::$model),
		];
		if($show)
			if(is_object(self::$library))
				self::$library->debug->print_r($return);
			else
				print_r($return);
		return $return;
	}
	private static function __protectHTTPS(bool $enableLocalhostHTTPS){
		self::setError();
		$httpsOn = !isset($_SERVER["HTTPS"])?false:($_SERVER["HTTPS"]==='on'?true:false);
		$localhost = $_SERVER['SERVER_NAME']==='localhost'?true:($_SERVER['SERVER_NAME']==='127.0.0.1'?true:false);
		$httpsURL = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		if($httpsOn === false and ($localhost === false or $enableLocalhostHTTPS === true))
			header('location: '.$httpsURL);
	}
	private static function __createReversion(string $scriptPath, string $corePath){
		self::setError();
		$initFilePathExplode = explode(DIRECTORY_SEPARATOR, str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $scriptPath));
		$scriptPath = explode(DIRECTORY_SEPARATOR, str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $corePath));
		foreach($initFilePathExplode as $id => $pathName)
			if($pathName === $scriptPath[$id])
				unset($scriptPath[$id], $initFilePathExplode[$id]);
			else
				break;
		unset($scriptPath[array_keys($scriptPath)[count($scriptPath)-1]]);
		$reversion = str_repeat('..'.DIRECTORY_SEPARATOR, count($initFilePathExplode)).implode(DIRECTORY_SEPARATOR, $scriptPath).DIRECTORY_SEPARATOR;
		if($reversion[0] === DIRECTORY_SEPARATOR)
			$reversion = substr($reversion, 1, strlen($reversion));
		while(strpos($reversion, DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR) <> false)
			$reversion = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $reversion);
		return $reversion;
	}
}
?>