<?php
return new class(){ //create class
	public function __construct(){ //construct
		$config = core::$module_add['bootstrap']['config']; //load config to variable
		$module_list = array_keys(core::$module_add); //module list
		$search = array_search('smarty', $module_list); //search smarty
		if($search === false) //if smarty not exists
			return core::setError(1, 'smarty not exists'); //return error 1
		$smarty = core::$module['smarty']->smarty; //get smarty
		$bootstrap = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
		<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>';
		$smarty->assign('bootstrap', $bootstrap); //assign bootstrap
	}
}
?>