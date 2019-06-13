<?php
require_once('../core/core.php');
$core = new Core();
if(isset($_POST['install'])){
	echo '<div>';
	$admin_login = htmlspecialchars($_POST['admin_login']);
	$admin_password = htmlspecialchars($_POST['admin_password']);
	if(strlen($admin_login) < 5){
		echo 'Login musi posiadać więcej niż 5 znaków';
	}else{
		$db = $core->library->db;
		$db->createTable('manager_user', ['id AUTOINCREMENT', 'login', 'password', 'perm']);
		$db->addData('manager_user', ['login' => $admin_login, 'password' => $core->library->crypt->exHash($admin_password), 'perm' => '1']);
		$db->createTable('core_autoconfig', ['id AUTOINCREMENT', 'name', 'value']);
		$db->createTable('manager_option', ['id AUTOINCREMENT', 'name', 'value']);
		$db->addData('manager_option', ['name' => 'module_show_dev_allConfig', 'value' => '0']);
		$db->addData('manager_option', ['name' => 'module_show_dev_allvardump', 'value' => '0']);
		$db->addData('manager_option', ['name' => 'dbeditor_hideTable', 'value' => '']);
		$db->addData('manager_option', ['name' => 'language', 'value' => 'pl']);
		$db->createTable('manager_user_permission', ['id AUTOINCREMENT', 'name', 'perm']);
		echo 'Poprawnie zainstalowano menadżera, nie zapomnij usunąć pliku <b>install.php</b>';
	}
	echo '<hr /></div>';
}
?>
<form method="POST">
	<h1 style="margin-bottom: 0px; margin-top: 0px;">Instalacja menadżera</h1>
	<h2 style="margin-bottom: 0px;">Konto administratora</h2>
	Login musi posiadać więcej niż 5 znaków
	<table>
		<tr>
			<td><input type="text" name="admin_login" placeholder="login" /></td>
			<td><input type="text" name="admin_password" placeholder="hasło" /></td>
		</tr>
	</table>
	<br />
	<input type="submit" name="install" value="Zainstaluj" />
</form>