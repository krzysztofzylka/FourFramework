<?php
if(!isset($_SESSION['fdbConnect']) or !isset($_GET['name']))
	header('location: ?page=fdbeditor&fdb=404');
$name = htmlspecialchars($_GET['name']);
if(isset($_POST['save'])){
	$data = [
		'option' => json_decode($_POST['option'], true),
		'column' => json_decode($_POST['column'], true),
		'data' => json_decode($_POST['data'], true),
	];
	core::$library->db->activeConnect = core::$library->global->read('fdbConnect');
	$tableData = core::$library->db->____saveDBFile($name, $data);
	core::$library->db->activeConnect = null;
}
core::$library->db->activeConnect = core::$library->global->read('fdbConnect');
$tableData = core::$library->db->____readDBFile($name, 'all', ['returnJSON' => true]);
core::$library->db->activeConnect = null;
?>
<div class='card-header'>
	Zaawansowana edycja tabeli <b><?php echo $name ?></b>
</div>
<div class='card-body'>
	<form method="POST">
		<b>option</b>
			<textarea style="width: 100%" rows='3' name="option"><?php echo $tableData['option'] ?></textarea>
		<b>column</b>
			<textarea style="width: 100%" rows='5' name="column"><?php echo $tableData['column'] ?></textarea>
		<b>data</b>
			<textarea style="width: 100%" rows='15' name="data"><?php echo $tableData['data'] ?></textarea>
			<input type="submit" value="Zapisz" name='save'>
	</form>
</div>