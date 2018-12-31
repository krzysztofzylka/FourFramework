<?php
$api = $this->core->_API();
$list = $this->get_list();
?>
<table>
	<tr><td><div style='width: 15px; height: 15px; background: green; border-radius: 8px;'></div></td><td>Aktywne</td></tr>
	<tr><td><div style='width: 15px; height: 15px; background: red; border-radius: 8px;'></div></td><td>Nieaktywne</td></tr>
	<tr><td><div style='width: 15px; height: 15px; background: blue; border-radius: 8px;'></div></td><td>Zainstalowane - nieaktywne</td></tr>
	<tr><td><div style='width: 15px; height: 15px; background: orange; border-radius: 8px;'></div></td><td>Wersja większa niż w API</td></tr>
	<tr><td><div style='width: 15px; height: 15px; background: purple; border-radius: 8px;'></div></td><td>Możliwa aktualizacja</td></tr>
</table>
<table width='100%' style='border: 1px solid black; border-collapse: collapse; margin-bottom: 5px;'>
	<?php
	for($x=0;$x<$api['count'];$x++){
		$data = $api['list'][$x];
		$opcje = '';
		if(in_array($data['name'], $list)) $opcje = '<a href="'.$this->generateLink('download--'.$data['download']).'">Aktualizuj ('.$data['version'].')</a>'; //dla aktualizacji
		elseif(strlen($data['download']) > 5) $opcje = '<a href="'.$this->generateLink('download--'.$data['download']).'">Pobierz</a>'; //dla pobierania
		$color = 'red';
		if(in_array($data['name'], $list)) $color = 'blue';
		if(in_array($data['name'], $this->core->module_list)) $color = 'green';
		$api_date = (int)strtotime($api['list'][$x]['date']);
		$mod_date = (int)strtotime($data['date']);
		if($api_date > $mod_date) $color = 'orange';
		if($api_date < $mod_date) $color = 'purple';
		echo "<tr>
			<td style='border-bottom: 1px solid black; margin: 0px; padding: 2px;' >
				<table width='100%'>
					<tr>
						<td width='15px'>
							<!-- status -->
							<div style='width: 15px; height: 15px; background: ".$color."; border-radius: 8px;'></div>
						</td>
						<td width='70%'>
							<!-- nazwa -->
							".$data['name']."
						</td>
						<td>
							<!-- wersja -->
							".$data['version']."
						</td>
					</tr>
					<tr>
						<td>
							<!-- PUSTE -->
						</td>
						<td>
							<!-- opis -->
							<i>
								".$data['description']."
							</i>
						</td>
						<td>
							<!-- opcje -->
							".$opcje."
						</td>
					</tr>
				</table>
			</td>
		</tr>";
	}
	?>
</table>