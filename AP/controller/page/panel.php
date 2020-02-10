<?php
return new class(){
	public function __construct(){
		if(file_exists('controller/user/panel.php'))
			header('location: index.php?p=panel');
		core::loadView('panel');
	}
}
?>