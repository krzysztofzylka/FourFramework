<?php
if(isset($_POST['optSave'])){
	//autostart
	if(isset($_POST['lib_network_autostart'])) $core->__autoConfigDB('lib_network_autostart', 1);
	else $core->__autoConfigDB('lib_network_autostart', 0);
	//curlTimeout
	if(isset($_POST['lib_network_curltimeout']))
		$core->__autoConfigDB('lib_network_curltimeout', htmlspecialchars($_POST['lib_network_curltimeout']));
	//methodManual
	if(isset($_POST['lib_network_methodManual'])) $core->__autoConfigDB('lib_network_methodManual', 1);
	else $core->__autoConfigDB('lib_network_methodManual', 0);
	//method
	if(isset($_POST['lib_network_method']))
		$core->__autoConfigDB('lib_network_method', htmlspecialchars($_POST['lib_network_method']));
}
?>
<h1>Konfiguracja biblioteki network</h1>
<form method="POST">
<table class="border">
	<tr>
		<td>
			<input type="checkbox" name="lib_network_autostart" <?php if((bool)$core->__autoConfigDB('lib_network_autostart') == true) echo 'checked' ?> />
		</td>
		<td>
			Automatyczne wczytywanie biblioteki oraz jego konfiguracji
		</td>
	</tr>
	<tr>
		<td>
			<input type="text" name="lib_network_curltimeout" value="<?php echo $core->__autoConfigDB('lib_network_curltimeout')===false?$core->library->network->curlTimeout:$core->__autoConfigDB('lib_network_curltimeout') ?>" />
		</td>
		<td>
			Maksymalny czas oczekiwania dla połączenia curl
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" name="lib_network_methodManual" <?php echo (bool)$core->__autoConfigDB('lib_network_methodManual')===false?'':'checked' ?> />
		</td>
		<td>
			Manualne wybranie typu łączenia
		</td>
	</tr>
	<tr>
		<td>
			<select name="lib_network_method">
				<option value="1" <?php if($core->__autoConfigDB('lib_network_method')=='1') echo 'selected' ?>>curl</option>
				<option value="2" <?php if($core->__autoConfigDB('lib_network_method')=='2') echo 'selected' ?>>file_get_contents</option>
			</select>
		</td>
		<td>
			Typ połączenia (jeżeli manualne)
		</td>
	</tr>
</table>
<input type="submit" name="optSave" value="Zapisz konfigurację" />
</form>