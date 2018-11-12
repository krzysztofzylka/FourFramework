<?php
//definiowanie głównych zmiennych
$core = $this->core;
$module = $core->module['account'];
$database = $core->module['database'];
$connect = $database->connect;
$config = $core->module_config['account'];
?>
<h1>AdminPanel modułu account</h1>
Zainstalowany moduł: <?php if(in_array($module->account['tableName'], $database->tableList())) echo 'TAK'; else echo 'NIE' ?><br />
<br />
Ilość użytkowników: <?php echo $connect->query('SELECT count(*) as count FROM '.$module->account['tableName'])->fetch(PDO::FETCH_ASSOC)['count'] ?><br />
<br />
Użytkownicy:<br />
<?php
$list = $connect->query('SELECT * FROM '.$module->account['tableName'])->fetchAll(PDO::FETCH_ASSOC);
if(count($list) > 0){
	$title = array_keys($list[0]);
	$array = [];
	foreach($list as $d){
		$array2 = [];
		foreach($d as $l => $a){
			if($l == $module->account['password']) $a = '******';
			array_push($array2, $a);
		}
		array_push($array, $array2);
	}
	$option = [
		'search' => true,
	];
	echo $core->module['table']->createTable($array, $title, $option);
	// echo "<table>
		// <tr>";
			// foreach($name as $data) echo "<th>".$data."</th>";
		// echo "</tr>";
		// foreach($list as $array){
			// echo "<tr>";
			// foreach($array as $name => $data){
				// if($module->account['password'] == $name) echo "<td>******</td>";
				// else echo "<td>".$data."</td>";
			// }
			// echo "</tr>";
		// }
	// echo "</table>";
}
?>