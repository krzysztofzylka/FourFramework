<?php
return new class(){
	public function __construct(){
		if(isset($_GET['debug']))
			core::loadView('framework_logi_debug');
		elseif(isset($_GET['file']))
			core::loadView('framework_logi_file');
		else
			core::loadView('framework_logi');
	}
}
?>