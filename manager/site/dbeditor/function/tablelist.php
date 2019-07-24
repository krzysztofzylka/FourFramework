<?php
if(isset($_GET['check'])){
	$check = $db->checkTable($_GET['check']);
	if($check==='')
		echo '<div class="message green">'.$lang->get('tableiscorrect').'</div>';
	else
		echo '<div class="message red">'.$lang->get('finderrorintable').': <br />'.$check.'</div>';
}

if(isset($_GET['delete'])){
	$db->deleteTable($_GET['delete']);
	echo '<div class="message green">'.$lang->get('successdeletetable').'</div>';
}

$tableList = $db->tableList();
$hideList = explode('|', optionRead('dbeditor_hideTable'));
$tableList = array_diff($tableList, $hideList);
echo '<table>
<tr>
	<th>'.$lang->get('name').'</th><th>'.$lang->get('size').'</th><th>'.$lang->get('option').'</th>
</tr>';
foreach($tableList as $name){
	echo '<tr>
		<td><a href="index.php?type=dbeditor&page=table&table='.$name.'">'.$name.'</a></td>
		<td>
			'.($core->libraryExists('memory')?$core->library->memory->formatBytes(filesize($DBpath.$name.'.FDB')):filesize($DBpath.$name.'.FDB')).'
		</td>
		<td>
			<a href="index.php?type=dbeditor&page=main&delete='.$name.'" onclick="return confirm(\''.$lang->get('uaresuredeletetable').' '.$name.'? '.$lang->get('optundone').'\')">'.$lang->get('delete').'</a> 
			<a href="index.php?type=dbeditor&page=main&func=tabele&check='.$name.'">'.$lang->get('check').'</a>
		</td>
	</tr>';
}
echo '</table>';
?>