<h1 class="bg"><?php echo $lang->get('dboption') ?></h1>
<div class="menu">
	<a href='index.php?type=dbeditor&page=main&func=tabele'><?php echo $lang->get('table') ?></a>
	<a href='index.php?type=dbeditor&page=main&func=createTable'><?php echo $lang->get('createtable') ?></a>
	<a href='index.php?type=dbeditor&page=main&func=script'><?php echo $lang->get('executescript') ?></a>
	<a href='index.php?type=dbeditor&page=main&func=option'><?php echo $lang->get('dboption') ?></a>
</div>
<hr />
<?php
$func = isset($_GET['func'])?htmlspecialchars($_GET['func']):'tabele';
switch($func){
	case 'script':
		if(isset($_POST['script'])){
			$return = $db->script(htmlspecialchars($_POST['script']));
			echo '<div class="message">'.$lang->get('executescript').': '.$_POST['script'].'<br />'.$lang->get('return').':<br />'.$return.'</div>';
		}
		echo '<form method="POST">
			<input style="width: 100%" name="script" />
			<input type="submit" style="width: 100%" value="'.$lang->get('execute').'" />
		</form>';
		break;
	case 'tabele':
		include('site/dbeditor/function/tablelist.php');
		break;
	case 'createTable':
		if(isset($_POST['createTable'])){
			unset($_POST['createTable']);
			$name = $_POST['name'];
			unset($_POST['name']);
			$data = [];
			foreach($_POST as $item)
				if($item <> '') array_push($data, $item);
			$db->createTable($name, $data);
			echo '<div class="message green">'.$lang->get('successcreatetable').' '.$name.'</div>';
		}
		echo '<form method="POST">
			<b>'.$lang->get('tablename').'</b><br />
			<input type="text" name="name" /><br />
			<br />
			<b>'.$lang->get('column').'</b><br />';
			for($i=1;$i<=10;$i++){
				echo $lang->get('column2').' '.$i.'<br /><input type="text" name="col_'.$i.'" /><br />';
			}
		echo '<br />
		<input type="submit" value="'.$lang->get('add').'" name="createTable" /></form>';
		break;
	case 'option':
		if(isset($_POST['saveOption'])){
			optionWrite('dbeditor_hideTable', htmlspecialchars($_POST['hideTable']));
			optionWrite('dbeditor_dbpath', htmlspecialchars($_POST['dbpath']));
		}
		echo '<form method="POST">
		<table>
			<tr>
				<td>'.$lang->get('hidetableindbeditor').'</td>
				<td><input type="text" value="'.optionRead('dbeditor_hideTable').'" name="hideTable"></td>
			</tr>
			<tr>
				<td>'.$lang->get('dbpath').'</td>
				<td><input type="text" value="'.(optionRead('dbeditor_dbpath')==""?$core->path['dir_db']:optionRead('dbeditor_dbpath')).'" name="dbpath"></td>
			</tr>
		</table>
		<input type="submit" value="'.$lang->get('saveoption').'" name="saveOption" />
		</form>';
		break;
}
?>