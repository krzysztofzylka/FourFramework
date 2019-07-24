<?php
function optionRead($name){
	global $db;
	$query = $db->getData('manager_option', ['name='.$name], false);
	return $query['value'];
}

function optionWrite($name, $value){
	global $db;
	$update = $db->updateData('manager_option', ['name='.$name], ['value='.$value]);
	if($update == 0 or $update == false){
		$db->addData('manager_option', ['name' => $name, 'value' => $value]);
	}
}
?>