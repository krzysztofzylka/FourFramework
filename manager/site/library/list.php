<h1>Lista bibliotek</h1>
<?php
$scan = scandir($core->path['dir_library']);
$scan = array_diff($scan, ['.', '..']);
$i = 1;
foreach($scan as $lib){
	$lib = str_replace('.php', '', $lib);
	echo $i.'. '.$lib.' '.(isset($core->library->$lib->version)?'('.@$core->library->$lib->version.')':'').'<br />';
	$i++;
}
?>