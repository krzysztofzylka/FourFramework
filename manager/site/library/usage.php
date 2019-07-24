<h1><?php echo $lang->get('libraryusage') ?></h1>
<table class="table title border">
	<tr>
		<td><?php echo $lang->get('name') ?></td>
		<td><?php echo $lang->get('size') ?></td>
		<td><?php echo $lang->get('ramusage') ?></td>
		<td><?php echo $lang->get('loadtime') ?></td>
	</tr>
	<?php
	$scan = scandir($core->path['dir_library']);
	$scan = array_diff($scan, ['.', '..']);
	foreach($scan as $lib){
		$name = str_replace('.php', '', $lib);
		$path = $core->path['dir_library'].$name.'.php';
		$ramStart = memory_get_usage(false);
		$time = microtime(true);
		$core->library->$name;
		$timeEnd = microtime(true)-$time;
		$ramUsage = memory_get_usage(false)-$ramStart;
		echo '<tr>
			<td>'.$name.'</td>
			<td align="center">'.round(filesize($path)/1024, 2).'kb</td>
			<td>'.round($ramUsage/1024, 2).'kb</td>
			<td>'.sprintf("%01.5f", $timeEnd).'</td>
		</tr>';
	}
	?>
</table>