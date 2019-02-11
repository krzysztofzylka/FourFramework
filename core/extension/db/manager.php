<?php
$list = $this->dbList();

if(isset($_POST['edit'])){
	$this->write($_GET['name'], $_POST['name'], $_POST['value']);
}elseif(isset($_POST['delete'])){
	$this->del($_GET['name'], $_POST['name']);
}

switch(true){
	case isset($_GET['name']):
		echo '<a href="?a=db">Return to database list</a><br /><br />';
		echo '<form method="POST">
		<b>Update/add to database:</b><br />
		Name: <input type="text" value="" name="name"/><br />
		Value: <input type="text" value="" name="value" /><br />
		<input type="submit" name="edit" value="Update/add" />
		</form><br />';
		$dblist = $this->readArray($_GET['name']);
		echo '<table border="1"> <tr> <th> Name </th> <th> Value </th> <th> Option </th> </tr>';
		foreach($dblist as $name => $value){
			echo '<tr>
				<td>'.$name.'</td>
				<td><form method="POST"><input type="text" value="'.$name.'" name="name" hidden/><input type="text" value="'.$value.'" name="value" /><input type="submit" name="edit" value="Save" /></form></td>
				<td><form method="POST"><input type="text" value="'.$name.'" name="name" hidden/><input type="submit" name="delete" value="Delete" /></form></td>
			</tr>';
		}
		echo '</table>';
		break;
	default:
		echo 'Database list:<br />';
		foreach($list as $name){
			$name = str_replace(".sdb", "", $name);
			echo '<a href="?a=db&name='.$name.'">'.$name.'</a><br />';
		}
		break;
}
?>