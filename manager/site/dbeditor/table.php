<?php
if(!isset($_GET['table'])){
	echo '<h1>Błąd</h1>
	Nie wybrano tabeli';
}else{
	$table = htmlspecialchars($_GET['table']);
	echo "<h1 class='bg'>Tabela: ".$table."</h1>
	<div class='menu'>
		<a href='index.php?type=dbeditor&page=table&table=".$table."&dbtype=info'>Informacje</a>
		<a href='index.php?type=dbeditor&page=table&table=".$table."&dbtype=data'>Dane</a>
		<a href='index.php?type=dbeditor&page=table&table=".$table."&dbtype=column'>Kolumny</a>
		<a href='index.php?type=dbeditor&page=table&table=".$table."&dbtype=add_data'>Dodaj dane</a>
	</div>
	<hr />";
	$type = isset($_GET['dbtype'])?htmlspecialchars($_GET['dbtype']):'data';
	switch($type){
		case 'data':
			if(isset($_GET['script'])){
				$script = htmlspecialchars($_GET['script']);
				$return = $db->script($script);
				echo '<div class="message">Wykonano skrypt<br />'.$script.'<br />
				Odpowiedź: '.$return.'</div>';
			}
			if(isset($_POST['add'])){
				unset($_POST['add']);
				$table = htmlspecialchars($_GET['table']);
				$db->addData($table, $_POST);
				echo '<div class="message">Poprawnie dodano dane</div>';
			}
			$column = $db->getColumnAdvList($table);
			if(is_array($column)){
				if($column===false){
					echo "Błąd odczytania tabeli";
					break;
				}
				$col = [];
				$ai = false;
				echo '<table class="border formFormat title">
					<tr>';
						foreach($column as $item){
							if($item['autoincrement'])
								$ai = $item['name'];
							echo '<th class="'.($ai==$item['name']?'dbid':'').'">'.$item['name'].'</th>';
							array_push($col, $item['name']);
						}
						echo '<th></th>
					</tr>';
					$data = $db->getData($table);
					foreach($data as $item){
						$opcje = '';
						if($ai)
							$opcje .= '<a href="index.php?type=dbeditor&page=table&table='.$table.'&dbtype=data&script=DELETE '.$table.' WHERE '.$ai.'='.$item[$ai].'">Usuń</a>';
						else{
							$opcje .= '<a href="index.php?type=dbeditor&page=table&table='.$table.'&dbtype=data&script=DELETE '.$table.' WHERE ';
							$lnk = '';
							foreach($item as $a => $b)
								$lnk .= $a.'='.$b.' and ';
							$chck = substr($lnk, strlen($lnk)-5, strlen($lnk));
							if($chck == ' and ')
								$lnk = substr($lnk, 0,  strlen($lnk)-5);
							$opcje .= $lnk;
							$opcje .= '">Usuń</a>';
						}
						echo '<tr>';
						foreach($col as $name){
							echo '<td>'.$item[$name].'</td>';
						}
						echo '<td>'.$opcje.'</td>
						</tr>';
					}
					echo '<tr><form method="POST">';
						foreach($column as $item)
							echo '<td><input name="'.$item['name'].'" '.($item['autoincrement']?'disabled':'').'/></td>';
					echo '<td><input type="submit" name="add" value="Dodaj" /></td>
					</form>
					</tr>';
					
			}
			break;
		case 'info':
			if(isset($_GET['opt_clear'])){
				$db->setOption($_GET['opt_clear'], 'clear');
				echo '<div class="message">Poprawnie wyszyczono tabelę '.$_GET['opt_clear'].'</div>';
			}
			if(isset($_GET['opt_crypt'])){
				$db->setOption($_GET['opt_crypt'], 'crypt');
				echo '<div class="message">Poprawnie zaszyfrowano tabelę '.$_GET['opt_crypt'].'</div>';
			}
			if(isset($_GET['opt_decrypt'])){
				$db->setOption($_GET['opt_decrypt'], 'decrypt');
				echo '<div class="message">Poprawnie odszyfrowano tabelę '.$_GET['opt_decrypt'].'</div>';
			}
			$start = microtime(true);
			$info = $db->getDBInformaction($table);
			$end = microtime(true);
			$time = $end-$start;
			echo '<table class="title border twoData">
				<tr>
					<th>Nazwa</th>
					<th>Wartość</th>
				</tr>
				<tr>
					<td>Nazwa tabeli</td>
					<td>'.$info['name'].' <a href="index.php?type=dbeditor&page=table&table='.$table.'&dbtype=info&opt_clear='.$table.'" onclick="return confirm(\'Czy na pewno chcesz wyczyścić tabelę '.$_GET['table'].'? spowoduje to usunięcie z niej wszystkich danych oraz wyzerowanie licznika. Tej operacji nie można cofnąć\')">[Wyczyść]</a></td>
				</tr>
				<tr>
					<td>Wersja</td>
					<td>'.$info['version'].'</td>
				</tr>
				<tr>
					<td>Szyfrowanie</td>
					<td>'.
						($info['crypt']?'Tak':'Nie').' ';
						if($info['crypt'])
							echo '<a href="index.php?type=dbeditor&page=table&table='.$table.'&dbtype=info&opt_decrypt='.$table.'">[Odszyfruj]</a>';
						else
							echo '<a href="index.php?type=dbeditor&page=table&table='.$table.'&dbtype=info&opt_crypt='.$table.'">[Szyfruj]</a>';
					echo '</td>
					</tr>
					<tr>
						<td>Ostatnie użycie</td>
						<td>'.$info['lastUse'].'</td>
					</tr>
					<tr>
						<td>Rozmiar pliku</td>
						<td>'.$core->library->memory->formatBytes(filesize($core->path['dir_db'].$table.'.FDB')).'</td>
					</tr>
					<tr>
						<td>Prędkość wykonania</td>
						<td>'.round($time, 5).' sec</td>
					</tr>
					<tr>
						<td>Uprawnienia</td>
						<td>'.$info['perms'].' '.($info['perms']!=='0600'?'(Niezabezpieczone, napraw bazę aby ustawić zabezpieczenie)':'').'</td>
					</tr>
			</table>';
			break;
		case 'column':
			$column = $db->getColumnAdvList($table);
			$info = $db->getDBInformaction($table);
			echo '<table class="border title twoData">
				<tr><th>Nazwa</th><th>Licznik</th></tr>';
				foreach($column as $item){
					echo '<tr>
						<td '.($item['autoincrement']?'style="text-decoration: underline"':'').'>'.$item['name'].'</td>
						<td>'.($item['autoincrement']?$item['count']:'-').'</td>
					</tr>';
				}
			echo '</table>';
			break;
		case 'add_data':
			if(isset($_POST['add_data'])){
				$data = [];
				foreach($_POST as $name => $value){
					$prefix = substr($name, 0, 7);
					if($prefix == "column_"){
						$name2 = str_replace('column_', '', $name);
						switch($_POST['option_'.$name2]){
							case 'exhash':
								$value = $core->library->crypt->exHash($value);
								break;
							case 'md5':
								$value = md5($value);
								break;
							case 'date_atom':
								$value = date(DATE_ATOM);
								break;
							case 'time';
								$value = time();
								break;
						}
						$data[$name2] = $value;
					}
				}
				$query = $core->library->db->addData($table, $data);
				if($query)
					echo '<div class="message green">Poprawnie dodano dane do tabeli</div>';
				else
					echo '<div class="message red">Błąd dodawania danych do tabeli</div>';
			}
			echo '<form method="POST"><table class="title">
				<tr>
					<td>Nazwa</td>
					<td>Wartość</td>
					<td>Opcja</td>
				</tr>';
				$column = $db->getColumnAdvList($table);
				foreach($column as $item){
					echo '<tr>
						<td '.($item['autoincrement']?'style="text-decoration: underline"':'').'>'.$item['name'].'</td>
						<td><input type="text" name="column_'.$item['name'].'" '.($item['autoincrement']?'disabled':'').'/></td>
						<td>
							<select name="option_'.$item['name'].'" '.($item['autoincrement']?'disabled':'').'>
								<option value="text">Tekst</option>
								<option value="md5">md5</option>
								<option value="exhash">exHash</option>
								<option value="date_atom">DATE_ATOM</option>
								<option value="time">time()</option>
							</select>
						</td>
					</tr>';
				}
			echo '</table>
			<input type="submit" name="add_data" value="Dodaj" />
			</form>';
			break;
	}
}
?>