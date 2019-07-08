<h1><?php echo $lang->get('checkframeworkfile') ?></h1>
<?php
function getinfo($pth){
	global $lang, $core;
	echo '<table>
	<tr>
		<th>'.$lang->get('path').'</th><th>'.$lang->get('size').'</th><th>'.$lang->get('lastedit').'</th>
	</tr>';
	$scan = $core->library->file->scanDir($pth);
	foreach($scan as $path){
		$path2 = $pth.$path;
		echo "<tr>
			<td>".$path."</td>
			<td>".(is_file($path2)?filesize($path2).'B':'-')."</td>
			<td>".$core->library->file->getFileUpdateDate($path2)."</td>
		</tr>";
	}
	echo '</table>';
}

echo '<h2>Core</h2>';
getinfo($core->path['dir_core']);
echo '<h2>Manager</h2>';
if(file_exists($core->reversion.'manager/')){
getinfo($core->reversion.'manager/');
}
?>