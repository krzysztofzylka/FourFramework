<?php
return new class(){
	public function __construct(){
		core::$module['account']->logoutUser();
		header('location: index.php');
	}
}
?>