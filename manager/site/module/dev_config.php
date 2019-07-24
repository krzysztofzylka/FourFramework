<?php
$name = htmlspecialchars($_GET['name']);
?>
<h1><?php echo $lang->get('dev') ?>: <?php echo $lang->get('viewmoduleconfig') ?> <?php echo $name ?></h1>
<table class="title border twoData">
	<tr>
		<td><?php echo $lang->get('name') ?></td>
		<td><?php echo $lang->get('value') ?></td>
	</tr>
	<?php
	$path = $core->path['dir_module'].$name.'/';
	$config = include($path.'config.php');
	foreach($config as $name => $value){
		if(is_array($value)){
			if($name == "include"){
				$data = "";
				foreach($value as $fname){
					$data .= $fname.' ('.(file_exists($path.$fname)?$lang->get('find'):$lang->get('nofind')).')'.PHP_EOL;
				}
				$value = $data;
			}else
				$value = "{array}";
		}
		if($name == 'main_file')
			$value = $value.' ('.(file_exists($path.$value)?$lang->get('find'):$lang->get('nofind')).')';
		echo '<tr>
			<td>'.$name.'</td>
			<td>'.$value.'</td>
		</tr>';
	}
	?>
</table>