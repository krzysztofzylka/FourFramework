<h1>Pobieranie modułów z serwera</h1>
<table class="title border">
	<tr>
		<td>Nazwa</td>
		<td>Opis</td>
		<td>Wersja</td>
		<td>Język</td>
		<td>Rozmiar</td>
		<td>Opcje</td>
	</tr>
	<?php
	$api = $core->_API();
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
					echo '<a class="button disabled" href="#">Pobierz</a>';
				else
					echo '<a class="button" href="index.php?type=module&page=install&uid='.$module['uid'].'">Pobierz</a>';
			echo '</td>
		</tr>';
	}
	?>
</table>