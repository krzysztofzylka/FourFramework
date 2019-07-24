<?php
if(!isset($_GET['table'])){
	echo '<h1>'.$lang->get('error').'</h1>
	'.$lang->get('noselecttable').'';
}else{
	$table = htmlspecialchars($_GET['table']);
	echo "<h1 class='bg'>".$lang->get('table2').": ".$table."</h1>
	<div class='menu'>
		<a href='index.php?type=dbeditor&page=table&table=".$table."&dbtype=info'>".$lang->get('info')."</a>
		<a href='index.php?type=dbeditor&page=table&table=".$table."&dbtype=data'>".$lang->get('data')."</a>
		<a href='index.php?type=dbeditor&page=table&table=".$table."&dbtype=column'>".$lang->get('column')."</a>
		<a href='index.php?type=dbeditor&page=table&table=".$table."&dbtype=add_data'>".$lang->get('adddata')."</a>
	</div>
	<hr />";
	$type = isset($_GET['dbtype'])?htmlspecialchars($_GET['dbtype']):'data';
	switch($type){
		case 'data':
			$starttime = microtime(true);
			if(isset($_GET['script'])){
				$script = htmlspecialchars($_GET['script']);
				$return = $db->script($script);
				echo '<div class="message">'.$lang->get('executescript').':<br />'.$script.'<br />
				'.$lang->get('return').': '.$return.'</div>';
			}
			if(isset($_POST['add'])){
				unset($_POST['add']);
				$table = htmlspecialchars($_GET['table']);
				$db->addData($table, $_POST);
				echo '<div class="message">'.$lang->get('successadddata').'</div>';
			}
			$column = $db->getColumnAdvList($table);
			if(is_array($column)){
				if($column===false){
					echo $lang->get('errorreadtable');
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
						if($ai){
							$opcje .= '<a href="index.php?type=dbeditor&page=table&table='.$table.'&dbtype=data&script=DELETE '.$table.' WHERE '.$ai.'='.$item[$ai].'">'.$lang->get('delete').'</a> ';
							$opcje .= '<a href="index.php?type=dbeditor&page=table&table='.$table.'&dbtype=edit_data&ai='.$item[$ai].'">'.$lang->get('edit').'</a>';
						}else{
							//delete
							$opcje .= '<a href="index.php?type=dbeditor&page=table&table='.$table.'&dbtype=data&script=DELETE '.$table.' WHERE ';
							$lnk = '';
							foreach($item as $a => $b)
								$lnk .= $a.'='.$b.' and ';
							$chck = substr($lnk, strlen($lnk)-5, strlen($lnk));
							if($chck == ' and ')
								$lnk = substr($lnk, 0,  strlen($lnk)-5);
							$opcje .= $lnk;
							$opcje .= '">'.$lang->get('delete').'</a> ';
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
					echo '<td><input type="submit" name="add" value="'.$lang->get('add').'" /></td>
					</form>
					</tr>';
			}
			$endtime = microtime(true); 
			// printf("Czas ładowania: %f sekund", $endtime - $starttime );
			break;
		case 'info':
			if(isset($_GET['opt_clear'])){
				$db->setOption($_GET['opt_clear'], 'clear');
				echo '<div class="message">'.$lang->get('successloadtable').' '.$_GET['opt_clear'].'</div>';
			}
			if(isset($_GET['opt_crypt'])){
				$db->setOption($_GET['opt_crypt'], 'crypt');
				echo '<div class="message">'.$lang->get('successcrypttable').' '.$_GET['opt_crypt'].'</div>';
			}
			if(isset($_GET['opt_decrypt'])){
				$db->setOption($_GET['opt_decrypt'], 'decrypt');
				echo '<div class="message">'.$lang->get('successdecrypttable').' '.$_GET['opt_decrypt'].'</div>';
			}
			$start = microtime(true);
			$info = $db->getDBInformaction($table);
			$end = microtime(true);
			$time = $end-$start;
			echo '<table class="title border twoData">
				<tr>
					<th>'.$lang->get('name').'</th>
					<th>'.$lang->get('value').'</th>
				</tr>
				<tr>
					<td>'.$lang->get('tablename').'</td>
					<td>'.$info['name'].' <a href="index.php?type=dbeditor&page=table&table='.$table.'&dbtype=info&opt_clear='.$table.'" onclick="return confirm(\''.$lang->get('cleartable').' '.$_GET['table'].'? '.$lang->get('cleartableext').'\')">['.$lang->get('clear').']</a></td>
				</tr>
				<tr>
					<td>'.$lang->get('version').'</td>
					<td>'.$info['version'].'</td>
				</tr>
				<tr>
					<td>'.$lang->get('crypt').'</td>
					<td>'.
						($info['crypt']?$lang->get('yes'):$lang->get('no')).' ';
						if($info['crypt'])
							echo '<a href="index.php?type=dbeditor&page=table&table='.$table.'&dbtype=info&opt_decrypt='.$table.'">['.$lang->get('decrypt').']</a>';
						else
							echo '<a href="index.php?type=dbeditor&page=table&table='.$table.'&dbtype=info&opt_crypt='.$table.'">['.$lang->get('encrypt').']</a>';
					echo '</td>
					</tr>
					<tr>
						<td>'.$lang->get('lastuse').'</td>
						<td>'.$info['lastUse'].'</td>
					</tr>
					<tr>
						<td>'.$lang->get('size').'</td>
						<td>'.$core->library->memory->formatBytes(filesize($core->path['dir_db'].$table.'.FDB')).'</td>
					</tr>
					<tr>
						<td>'.$lang->get('execspeed').'</td>
						<td>'.round($time, 5).' sec</td>
					</tr>
					<tr>
						<td>'.$lang->get('permission').'</td>
						<td>'.$info['perms'].' '.($info['perms']!=='0600'?'('.$lang->get('tablepermerror').')':'').'</td>
					</tr>
			</table>';
			break;
		case 'column':
			$column = $db->getColumnAdvList($table);
			$info = $db->getDBInformaction($table);
			echo '<table class="border title twoData">
				<tr><th>'.$lang->get('name').'</th><th>'.$lang->get('counter').'</th></tr>';
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
					echo '<div class="message green">'.$lang->get('successadddattotable').'</div>';
				else
					echo '<div class="message red">'.$lang->get('falseadddatatotable').'</div>';
			}
			echo '<form method="POST"><table class="title">
				<tr>
					<td>'.$lang->get('name').'</td>
					<td>'.$lang->get('value').'</td>
					<td>'.$lang->get('opt').'</td>
				</tr>';
				$column = $db->getColumnAdvList($table);
				foreach($column as $item){
					echo '<tr>
						<td '.($item['autoincrement']?'style="text-decoration: underline"':'').'>'.$item['name'].'</td>
						<td><input type="text" name="column_'.$item['name'].'" '.($item['autoincrement']?'disabled':'').'/></td>
						<td>
							<select name="option_'.$item['name'].'" '.($item['autoincrement']?'disabled':'').'>
								<option value="text">'.$lang->get('text').'</option>
								<option value="md5">md5</option>
								<option value="exhash">exHash</option>
								<option value="date_atom">DATE_ATOM</option>
								<option value="time">time()</option>
							</select>
						</td>
					</tr>';
				}
			echo '</table>
			<input type="submit" name="add_data" value="'.$lang->get('add').'" />
			</form>';
			break;
		case 'edit_data':
			$column = $db->getColumnAdvList($table);
			$ai = false;
			foreach($column as $item){
				if($item['autoincrement']){
					$ai = $item['name'];
					break;
				}
			}
			$ai_data = htmlspecialchars($_GET['ai']);
			if(isset($_POST['edit_data'])){
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
						array_push($data, $name2.'='.$value);
					}
				}
				$query = $core->library->db->updateData($table, [$ai.'='.$ai_data], $data);
				if($query)
					echo '<div class="message green">'.$lang->get('successeditdata').'</div>';
				else
					echo '<div class="message red">'.$lang->get('falseeditdata').'</div>';
			}
			echo '<form method="POST"><table class="title">
				<tr>
					<td>'.$lang->get('name').'</td>
					<td>'.$lang->get('value').'</td>
					<td>'.$lang->get('opt').'</td>
				</tr>';
				$data = $db->getData($table, [$ai.'='.$ai_data], false);
				foreach($column as $item){
					echo '<tr>
						<td '.($item['autoincrement']?'style="text-decoration: underline"':'').'>'.$item['name'].'</td>
						<td><input type="text" value="'.$data[$item['name']].'" name="column_'.$item['name'].'" '.($item['autoincrement']?'disabled':'').'/></td>
						<td>
							<select name="option_'.$item['name'].'" '.($item['autoincrement']?'disabled':'').'>
								<option value="text">'.$lang->get('text').'</option>
								<option value="md5">md5</option>
								<option value="exhash">exHash</option>
								<option value="date_atom">DATE_ATOM</option>
								<option value="time">time()</option>
							</select>
						</td>
					</tr>';
				}
			echo '</table>
			<input type="submit" name="edit_data" value="'.$lang->get('edit').'" />
			</form>';
			break;
	}
}
?>