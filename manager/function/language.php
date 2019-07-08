<?php
$lang = $core->module['language'];
$lang_name = "";
if(optionRead('language')===null)
	$lang_name == "pl";
else{
	$lang_name = optionRead('language');
	$check = 'module/language/lang/lang-'.$lang_name.'.php';
	if(!file_exists($check))
		$lang_name = "pl";
}
$lang->loadLang($lang_name);
?>