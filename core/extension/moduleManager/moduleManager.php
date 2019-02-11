<?php
return new class($this){
	protected $core;
	protected $path;
	protected $path_module;
	protected $network;
	public function __construct($obj){
		$this->core = $obj;
		$this->path = $obj->reversion.'core/extension/moduleManager/';
		$this->path_module = $obj->reversion.'module/';
		$this->network = $obj->library->network;
		include($this->path.'variable.php');
		$this->_autostart();
	}
	//get module list
	//0: all, 1: only active
	public function get_list(int $type=0) : array{
		//main table
		$array = array();
		//checking type
		switch($type){
			//all
			case 0:
				foreach(scandir($this->path_module) as $name){
					$path = $this->path_module.$name.'/';
					if(strpos($path, '.') > 0) continue;
					if(is_file($path) or $name == '.' or $name == '..' or !file_exists($path.'config.php')) continue;
					array_push($array, $name);
				}
				return $array;
				break;
			//only active
			case 1:
				return $this->core->module_list;
				break;
		}
	}
	//show module debug
	public function get_debug(string $name) : void{
		$object = $this->core->module[$name];
		if(method_exists($object, '__debugInfo')){
			if($this->debug['style'] == true){
				$this->get_debug_style($object->__debugInfo());
			}else{
				echo '<pre>';
				print_r($object->__debugInfo());
				echo '</pre>';
			}
		}else{
			echo 'Moduł nie posiada funkcji debugującej (__debugInfo)';
		}
		return;
	}
	//show module admin panel
	public function get_adminpanel(string $name) : void{
		$error = false;
		if(in_array($name, $this->core->module_list))
			$config = $this->core->module_config[$name];
		if(isset($config['adminpanel']))
			include($config['path'].$config['adminpanel']);
		else
			echo 'Moduł nie posiada panelu administracyjnego';
		return;
	}
	//generating debug table
	public function get_debug_style($array) : void{
		if(is_array($array)) {
			echo "<table border=1 cellspacing=0 cellpadding=3 width=100%>";
			echo '<tr><td colspan=2 style="background-color:#333333;padding:2px"><strong><font color=white>ARRAY</font></strong></td></tr>';
			foreach ($array as $k => $v) {
					echo '<tr><td valign="top" style="width:40px;background-color:#F0F0F0; padding:2px">';
					echo '<strong>' . $k . "</strong></td><td style='padding:2px'>";
					$this->get_debug_style($v);
					echo "</td></tr>";
			}
			echo "</table>";
			return;
		}
		echo $array;
	}
	//show module manager
	public function manager() : void{
		include($this->path.'function/manager.php');
		include($this->path.'type/'.$this->manager['view'].'.php');
		return;
	}
	//show module download manager
	public function download_manager() : void{
		include($this->path.'function/manager.php');
		include($this->path.'type/'.$this->manager['view'].'_download.php');
		return;
	}
	//autostart module
	protected function _autostart() : void{
		$read = $this->core->db->read('core', 'extension_moduleManager_autostart');
		foreach(explode(',', $read) as $name) if($name <> '')
			$this->core->loadModule($name);
		return;
	}
	//generating link
	public function generateLink(string $get_data='') : string{
		$get = $this->urlGetData;
		$query = $_SERVER['QUERY_STRING'];
		$explode = explode('&', $query);
		foreach($explode as $name=>$data)
			$explode[$name] = explode('=', $data);
		$search = 0;
		foreach($explode as $id => $data){
			if($data[0] == $get){
				$search = 1;
				$explode[$id][1] = $get_data;
				break;
			}
		}
		if($search == 0)
			array_push($explode, array($get, $get_data));
		foreach($explode as $id => $arr)
			$explode[$id] = implode('=', $arr);
		return '?'.implode('&', $explode);
	}
	//debug function
	public function __debugInfo() : array{
		return [
			'debug' => [
				'style' => $this->debug['style']?'true':'false',
			],
			'manager' => [
				'view' => $this->manager['view'],
				'getData' => $this->urlGetData,
			],
			'api' => [
				'active' => $this->vapi_use?'true':'false'
			],
			'path' => [
				'module' => $this->path_module,
			],
		];
	}
}
?>