<h1><?php echo $lang->get('downloadmodulefromserver') ?></h1>
<?php
$api = $core->_API();
	if($core->lastError['number'] > -1){
		echo $lang->get('errordownfromserver');
	}else{
?>
<table class="title border">
	<tr>
		<td><?php echo $lang->get('name'); ?></td>
		<td><?php echo $lang->get('description'); ?></td>
		<td><?php echo $lang->get('version'); ?></td>
		<td><?php echo $lang->get('lang'); ?></td>
		<td><?php echo $lang->get('size'); ?></td>
		<td><?php echo $lang->get('option'); ?></td>
	</tr>
	<?php
	foreach($api['list'] as $module){
		$disabled = false;
		if(file_exists($core->path['dir_module'].$module['name']))
			$disabled = true;
		echo '<tr>
			<td>'.$module['name'].'</td>
			<td>'.$module['description'].'</td>
			<td>'.$module['version'].'</td>
			<td>'.$module['language'].'</td>
			<td>'.($core->libraryExists('memory')==true?$core->library->memory->formatBytes($module['size']):$module['size']).'</td>
			<td>';
				if($disabled)
					echo '<a class="button disabled" href="#">'.$lang->get('download').'</a>';
				else
					echo '<a class="button" href="index.php?type=module&page=install&uid='.$module['uid'].'">'.$lang->get('download').'</a>';
			echo '</td>
		</tr>';
	}
	?>
</table>
<?php } ?>