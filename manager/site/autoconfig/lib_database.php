<?php
if(isset($_POST['optSave'])){
	//autostart
	if(isset($_POST['lib_database_autostart'])) $core->__autoConfigDB('lib_database_autostart', 1);
	else $core->__autoConfigDB('lib_database_autostart', 0);
	//advlog
	if(isset($_POST['lib_database_advlog'])) $core->__autoConfigDB('lib_database_advlog', 1);
	else $core->__autoConfigDB('lib_database_advlog', 0);
	//autoconnect
	if(isset($_POST['lib_database_autoconnect'])) $core->__autoConfigDB('lib_database_autoconnect', 1);
	else $core->__autoConfigDB('lib_database_autoconnect', 0);
	//connect
	if(isset($_POST['lib_database_connect_type']))
		$core->__autoConfigDB('lib_database_connect_type', htmlspecialchars($_POST['lib_database_connect_type']));
	if(isset($_POST['lib_database_connect_host']))
		$core->__autoConfigDB('lib_database_connect_host', htmlspecialchars($_POST['lib_database_connect_host']));
	if(isset($_POST['lib_database_connect_name']))
		$core->__autoConfigDB('lib_database_connect_name', htmlspecialchars($_POST['lib_database_connect_name']));
	if(isset($_POST['lib_database_connect_sqlite']))
		$core->__autoConfigDB('lib_database_connect_sqlite', htmlspecialchars($_POST['lib_database_connect_sqlite']));
	if(isset($_POST['lib_database_connect_port']))
		$core->__autoConfigDB('lib_database_connect_port', htmlspecialchars($_POST['lib_database_connect_port']));
	if(isset($_POST['lib_database_connect_login']))
		$core->__autoConfigDB('lib_database_connect_login', htmlspecialchars($_POST['lib_database_connect_login']));
	if(isset($_POST['lib_database_connect_password']))
		$core->__autoConfigDB('lib_database_connect_password', htmlspecialchars($_POST['lib_database_connect_password']));
}
if(isset($_POST['testConn'])){
	$core->library->database->connError = false;
	$config = [
		'type' => $core->__autoConfigDB('lib_database_connect_type'),
		'host' => $core->__autoConfigDB('lib_database_connect_host'),
		'name' => $core->__autoConfigDB('lib_database_connect_name'),
		'login' => $core->__autoConfigDB('lib_database_connect_login'),
		'password' => $core->__autoConfigDB('lib_database_connect_password'),
		'sqlite' => $core->__autoConfigDB('lib_database_connect_sqlite'),
		'port' => $core->__autoConfigDB('lib_database_connect_port'),
		'charset' => 'utf8'
	];
	$core->library->database->connect($config);
	if($core->lastError['number'] == -1){
		echo '<div class="message green">'.$lang->get('connectsuccess').'</div>';
	}else{
		echo '<div class="message red">'.$lang->get('connecterror').':<br />'.$lang->get('name').': '.$core->lastError['name'].'<br />'.$lang->get('description').': '.$core->lastError['message'].'</div>';
	}
}
?>
<h1><?php echo $lang->get('configurationlibrary2') ?> database</h1>
<form method="POST">
<table class="border">
	<tr>
		<td>
			<input type="checkbox" name="lib_database_autostart" <?php if((bool)$core->__autoConfigDB('lib_database_autostart') == true) echo 'checked' ?> />
		</td>
		<td>
			<?php echo $lang->get('autoloadlibandconf'); ?>
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" name="lib_database_advlog" <?php if((bool)$core->__autoConfigDB('lib_database_advlog') == true) echo 'checked' ?> />
		</td>
		<td>
			<?php echo $lang->get('saveadvlog'); ?>
		</td>
	</tr>
	<tr>
		<td colspan=2><b><?php echo $lang->get('connect'); ?></b></td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" name="lib_database_autoconnect" <?php if((bool)$core->__autoConfigDB('lib_database_autoconnect') == true) echo 'checked' ?> />
		</td>
		<td>
			<?php echo $lang->get('autodbconnect'); ?>
		</td>
	</tr>
	<tr>
		<td>
			<select name="lib_database_connect_type">
				<option value="mysql" <?php if($core->__autoConfigDB('lib_database_connect_type')=='mysql') echo 'selected' ?>>MySQL</option>
				<option value="sqlite" <?php if($core->__autoConfigDB('lib_database_connect_type')=='sqlite') echo 'selected' ?>>SQLite</option>
				<option value="postgresql" <?php if($core->__autoConfigDB('lib_database_connect_type')=='postgresql') echo 'selected' ?>>PostgreSQL</option>
				<option value="oracle" <?php if($core->__autoConfigDB('lib_database_connect_type')=='oracle') echo 'selected' ?>>Oracle</option>
			</select>
		</td>
		<td>
			<?php echo $lang->get('dbtype'); ?>
		</td>
	</tr>
	<tr>
		<td>
			<input type="text" name="lib_database_connect_sqlite" value="<?php echo $core->__autoConfigDB('lib_database_connect_sqlite') ?>" />
		</td>
		<td>
			<?php echo $lang->get('dbpath'); ?> <b>(SQLite)</b>
		</td>
	</tr>
	<tr>
		<td>
			<input type="text" name="lib_database_connect_host" value="<?php echo $core->__autoConfigDB('lib_database_connect_host') ?>" />
		</td>
		<td>
			<?php echo $lang->get('host'); ?> <b>(MySQL, PostgreSQL)</b>
		</td>
	</tr>
	<tr>
		<td>
			<input type="text" name="lib_database_connect_port" value="<?php echo $core->__autoConfigDB('lib_database_connect_port') ?>" />
		</td>
		<td>
			<?php echo $lang->get('port'); ?> <b>(PostgreSQL)</b>
		</td>
	</tr>
	<tr>
		<td>
			<input type="text" name="lib_database_connect_name" value="<?php echo $core->__autoConfigDB('lib_database_connect_name') ?>" />
		</td>
		<td>
			<?php echo $lang->get('dbname'); ?> <b>(MySQL, PostgreSQL, Oracle)</b>
		</td>
	</tr>
	
	<tr>
		<td>
			<input type="text" name="lib_database_connect_login" value="<?php echo $core->__autoConfigDB('lib_database_connect_login') ?>" />
		</td>
		<td>
			<?php echo $lang->get('login'); ?> <b>(MySQL, PostgreSQL, Oracle)</b>
		</td>
	</tr>
	<tr>
		<td>
			<input type="text" name="lib_database_connect_password" value="<?php echo $core->__autoConfigDB('lib_database_connect_password') ?>" />
		</td>
		<td>
			<?php echo $lang->get('password'); ?> <b>(MySQL, PostgreSQL, Oracle)</b>
		</td>
	</tr>
</table><br />
<input type="submit" name="optSave" value="<?php echo $lang->get('saveoption'); ?>" />
</form>
<br />
<form method="POST">
	<input type="submit" value="<?php echo $lang->get('testconnect'); ?>" name="testConn" />
</form>