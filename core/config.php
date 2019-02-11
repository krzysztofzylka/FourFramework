<?php
$config = $this->db->readArray('core/config');
if(is_array($config)){
	foreach($config as $name => $value){
		switch($name){
			case 'log_save':
				$this->log_save = $value ?? $this->log_save;
				break;
			case 'API_secure':
				if($value <> null){
					$this->API_secure = (bool)$value;
					$url = $this->API;
					$url = str_replace('https', '', $url);
					$url = str_replace('http', '', $url);
					$this->API = ($this->API_secure==true?'https':'http').$url;
				}
				break;
			case 'log_hide_type':
				$this->log_hide_type = $value==null?$this->log_hide_type:explode(',', $value);
				break;
		}
	}
}