<?php
if(isset($_POST['optSave'])){
	//autostart
	if(isset($_POST['lib_crypt_autostart'])) $core->__autoConfigDB('lib_crypt_autostart', 1);
	else $core->__autoConfigDB('lib_crypt_autostart', 0);
	//salt
	if(isset($_POST['lib_crypt_salt']))
		$core->__autoConfigDB('lib_crypt_salt', htmlspecialchars($_POST['lib_crypt_salt']));
}
?>
<h1>Konfiguracja biblioteki database</h1>
<form method="POST">
<table class="border">
	<tr>
		<td>
			<input type="checkbox" name="lib_crypt_autostart" <?php if((bool)$core->__autoConfigDB('lib_crypt_autostart') == true) echo 'checked' ?> />
		</td>
		<td>
			Automatyczne wczytywanie biblioteki oraz jego konfiguracji
		</td>
	</tr>
	<tr>
		<td>
			<input type="text" name="lib_crypt_salt" maxlength="16" value="<?php echo $core->__autoConfigDB('lib_crypt_salt')===false?$core->library->crypt->salt:$core->__autoConfigDB('lib_crypt_salt') ?>" />
		</td>
		<td>
			Sól kodowania (16 znaków)
		</td>
	</tr>
</table>
<input type="submit" name="optSave" value="Zapisz konfigurację" />
</form>