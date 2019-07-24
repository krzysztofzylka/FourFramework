<?php
// echo "asdasfas";
return new class(){ //create class
	public function __construct(){ //construct
		$config = core::$module_add['bootstrap']['config']; //load config to variable
		$module_list = array_keys(core::$module_add); //module list
		$search = array_search('smarty', $module_list); //search smarty
		if($search === false) //if smarty not exists
			return core::setError(1, 'smarty not exists'); //return error 1
		$smarty = core::$module['smarty']->smarty; //get smarty
		$bootstrap = '<link rel="stylesheet" href="'.$config['path'].'bootstrap/bootstrap.min.css">
		<script src="'.$config['path'].'bootstrap/jquery.min.js"></script>
		<script src="'.$config['path'].'bootstrap/bootstrap.min.js"></script>';
		$smarty->assign('bootstrap', $bootstrap); //assign bootstrap
	}
}
?>