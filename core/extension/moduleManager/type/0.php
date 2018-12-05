<table width='100%' style='border: 1px solid black; border-collapse: collapse;'>
	<?php
	foreach($this->get_list() as $name){
		$path = $this->path_module.$name.'/';
		$config = include($path.'config.php');
		if(!isset($config['name']) or $config['name'] == '') $config['name'] = $name;
		$color = 'red';
		if(in_array($name, $this->core->module_list)) $color = 'green';
		$opcje = '';
		if($color == 'red') $opcje .= '<a href="'.$this->generateLink('on--'.$name).'">Włącz</a> ';
		else $opcje .= '<a href="'.$this->generateLink('off--'.$name).'">Wyłącz</a> ';
		if($color == 'green') if(method_exists($this->core->module[$name], '__debugInfo')) $opcje .= '<a href="'.$this->generateLink('debug--'.$name).'">Debug</a>';
		$api_data = '';
		if($this->vapi_use or $this->vapi_offline){
			$api = $this->api_get(-1, $config['uid']);
			if($api['count'] == 1){
				$api_date = (int)strtotime($api['list']['date']);
				$mod_date = (int)strtotime($config['date']);
				if($api_date > $mod_date){ //dostępna aktualizacja
					$color = 'orange';
					$api_data = "(Dostępna aktualizacja)";
				}
				if($api_date < $mod_date) $color = 'purple';
			}
		}
		
		echo "<tr>
			<td style='border-bottom: 1px solid black; margin: 0px; padding: 0px;'>
				<table width='100%'>
					<tr>
						<td width='15px'>
							<!-- status -->
							<div style='width: 15px; height: 15px; background: ".$color."; border-radius: 8px;'></div>
						</td>
						<td width='70%'>
							<!-- nazwa -->
							".$config['name']."
						</td>
						<td>
							<!-- wersja -->
							".$config['version']." <i>".$api_data."</i>
						</td>
					</tr>
					<tr>
						<td>
							<!-- PUSTE -->
						</td>
						<td>
							<!-- opis -->
							<i>
								".$config['description']."
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
<?php
if(isset($_GET[$this->urlGetData])){
	$get = $_GET[$this->urlGetData];
	$explode = explode('--', $get);
	switch($explode[0]){
		case 'debug':
			$this->get_debug($explode[1]);
			break;
	}
}
?>