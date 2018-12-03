<?php
class core_db_bakj98D{
	protected $core;
	protected $path = '';
	public function __construct($obj){
		$this->core = $obj;
		$this->path = $obj->reversion.'core/extension/db/base/';
		if(!file_exists($this->path)) mkdir($this->path);
	}
	public function write($db_name, $name, $value){
		$file = $this->path.$db_name.'.php';
		$temp = array();
		if(file_exists($file)){
			$temp = unserialize(include($file));
		}
		$temp[$name] = $value;
		$serialize = '<?php return \''.serialize($temp).'\' ?>';
		file_put_contents($file, $serialize);
		return true;
	}
	public function read($db_name, $name){
		$file = $this->path.$db_name.'.php';
		if(!file_exists($file)) return false;
		$temp = unserialize(include($file));
		if(isset($temp[$name])) return $temp[$name];
		return null;
	}
	public function del($db_name, $name){
		$file = $this->path.$db_name.'.php';
		$temp = array();
		if(file_exists($file)){
			$temp = unserialize(include($file));
		}
		unset($temp[$name]);
		if(count($temp) == 0) return unlink($file);
		$serialize = '<?php return \''.serialize($temp).'\' ?>';
		file_put_contents($file, $serialize);
		return true;
	}
	public function check($db_name, $name){
		$file = $this->path.$db_name.'.php';
		if(!file_exists($file)) return false;
		$temp = unserialize(include($file));
		return isset($temp[$name])?true:false;
	}
}