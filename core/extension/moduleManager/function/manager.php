<?php
if(isset($_GET[$this->urlGetData])){
	$get = $_GET[$this->urlGetData];
	$explode = explode('--', $get);
	switch($explode[0]){
		case 'on':
			$on = explode('|', $this->core->db->read('core', 'extension_moduleManager_autostart'));
			if(in_array($explode[1], $on)) return header('location: '.$this->generateLink());
			array_push($on, $explode[1]);
			$this->core->db->write('core', 'extension_moduleManager_autostart', implode(',', $on));
			header('location: '.$this->generateLink());
			break;
		case 'off':
			$off = explode(',', $this->core->db->read('core', 'extension_moduleManager_autostart'));
			if(!in_array($explode[1], $off)) return header('location: '.$this->generateLink());
			$search = array_search($explode[1], $off);
			unset($off[$search]);
			$this->core->db->write('core', 'extension_moduleManager_autostart', implode(',', $off));
			header('location: '.$this->generateLink());
			break;
	}
}
?>