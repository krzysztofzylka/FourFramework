<?php
if(isset($_POST['option_module'])){
	if(isset($_POST['module_show_dev_allConfig']))
		optionWrite('module_show_dev_allConfig', '1');
	else
		optionWrite('module_show_dev_allConfig', '0');
	if(isset($_POST['module_show_dev_allvardump']))
		optionWrite('module_show_dev_allvardump', '1');
	else
		optionWrite('module_show_dev_allvardump', '0');
	echo '<div class="message green">Poprawnie zapisano ustawienia dla modułów</div>';
}
?>
<h1>Opcje menadżera</h1>
<br />
<form method="POST">
<b>Moduły</b><br />
<table>
	<tr>
		<td>
			<input type="checkbox" name="module_show_dev_allConfig" <?php echo optionRead('module_show_dev_allConfig')==1?'checked':'' ?> />
		</td>
		<td>
			Możliwość podglądnięcia pliku konfiguracji modułu <b>(programista)</b>
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" name="module_show_dev_allvardump" <?php echo optionRead('module_show_dev_allvardump')==1?'checked':'' ?> />
		</td>
		<td>
			Podglądnięcie danych modułu (var_dump) <b>(programista)</b>
		</td>
	</tr>
</table>
<input type="submit" name="option_module" value="Zapisz" />
</form>