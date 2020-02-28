<?php
//by Krzysztof Żyłka
//programista.vxm.pl/fourframework
class core{
	public static $error = [-1, '', '', null];
	public static $info = [
		'version' => '0.2.11 Alpha',
		'releaseDate' => '28.02.2020',
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
		'log' => 'core/base/log/'
	];
	public static $controller = [];
	public static $model = [];
	public static $module = [];
	public static $module_add = [];
	public static $library = [];
	public static $debug = [
		'showError' => True,
		'saveError' => True,
		'showCoreError' => True,
		'saveCoreError' => True,
	];
	private static $loadMultipleModule = false;
	public static function init(array $option = []){
		self::setError();
		if(!isset($option['autoCreatePath']))
			$option['autoCreatePath'] = true;
		if(isset($option['multipleModule']))
			self::$loadMultipleModule = boolval($option['multipleModule']);
		if(!isset($option['moveToHttps']))
			$option['moveToHttps'] = false;
		//zabezpieczenie przenoszące na https jeżeli strona została odpalona na http
		if($option['moveToHttps']){
			if(@($_SERVER["HTTPS"] != 'on'))
				header('location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		}
		//generowanie ścieżki reversion
		$inc = get_included_files();
		$first = str_replace('\\', '/', $inc[0]); //pobranie scieżki pierwszego wczytanego pliku
		$last = str_replace('core/core.php', '', str_replace('\\', '/', $inc[count($inc)-1])); //pobranie scieżki include (rdzeń) oraz usunięcie nazwy oraz folderu rdzenia
		$string = str_replace($last, '', $first);
		$repeatCounter = count(explode("/", $string))-1;
		self::$info['reversion'] = str_repeat('../', $repeatCounter);
		//generate fourframework path
		$frameworkPath = __DIR__.'/';
		$frameworkPath = substr($frameworkPath, 0, strlen($frameworkPath)-5);
		self::$info['frameworkPath'] = $frameworkPath;
		//error
		if(self::$debug['showError'])
			error_reporting(E_ALL);
		foreach(self::$path as $name => $value){
			self::$path[$name] = str_replace('\\', "/", self::$info['frameworkPath'].$value);
			if(!file_exists(self::$path[$name]) and $option['autoCreatePath'] === true)
				mkdir(self::$path[$name], 0700, true);
		}
		//debug
		if(self::$debug['saveError']){
			ini_set("log_errors", 1);
			$errorPath = self::$path['log'].'php_error_'.date('Ym').'.log';
			ini_set("error_log", $errorPath);
		}
		//include library class
		self::$library = include('library.php');
		//zabezpieczenie przed zbyt dużym plikiem php_error
		if(self::$debug['saveError']){
			$errorPath = self::$path['log'].'php_error_'.date('Ym').'.log';
			self::$library->file->protectLongFileSize($errorPath, 102400);
		}
		return true;
	}
	public static function setError(int $number=-1, string $name='', $description=''){
		self::$error = [$number, $name, $description, $number>-1?debug_backtrace():null];
		if(self::$debug['saveCoreError'] === true and $number > -1){
			$path = self::$path['log'].'core_error_'.date('Ymd').'.log';
			//Zabezpieczenie przed zbyt dużym plikiem > 100mb
			core::$library->file->protectLongFileSize($path, 102400);
			//generowanie danych do pliku
			$date = date('Y_m_d h:m:s');
			$data = '['.$date.'] ['.$number.'] ['.htmlspecialchars($name).'] ['.htmlspecialchars($description).'] ['.base64_encode(json_encode(self::$error[3])).']'.PHP_EOL;
			file_put_contents($path, $data, FILE_APPEND);
		}
		if(self::$debug['showCoreError'] === true and $number > -1){
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
		if(in_array($name, array_keys(self::$module)) and self::$loadMultipleModule === false)
			return self::setError(1, 'the class has already been loaded', '');
		$path = self::$path['module'].$name.'/';
		if(!file_exists($path.'config.php'))
			return self::setError(2, 'config file config.php not found', '');
		$arrayName = $name;
		$config = include($path.'config.php');
		$config['name'] = $name;
		$config['path'] = $path;
		if(in_array($name, array_keys(self::$module)) and self::$loadMultipleModule === true){
			$i = 2;
			while(true){
				$arrayName = $name.'_k'.$i;
				if(!isset(self::$module[$arrayName]))
					break;
				$i++;
			}
		}
		self::$module_add[$arrayName]['config'] = $config;
		if(!is_array($config))
			return self::setError(3, 'config file config.php error', 'the data returned is not a table');
		if(isset($config['include']) and is_array($config['include']))
			foreach($config['include'] as $name)
				include($path.$name);
		if(isset($config['moduleFile']) and !file_exists($path.$config['moduleFile']))
			return self::setError(4, 'module file not found', $path.$config['moduleFile']);
		else
			self::$module[$arrayName] = include($path.$config['moduleFile']);
		return self::$module[$arrayName];
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