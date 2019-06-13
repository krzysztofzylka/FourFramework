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
<h1><?php echo $lang->get('configurationlibrary2') ?> crypt</h1>
<form method="POST">
<table class="border">
	<tr>
		<td>
			<input type="checkbox" name="lib_crypt_autostart" <?php if((bool)$core->__autoConfigDB('lib_crypt_autostart') == true) echo 'checked' ?> />
		</td>
		<td>
			<?php echo $lang->get('autoloadlibandconf'); ?>
		</td>
	</tr>
	<tr>
		<td>
			<input type="text" name="lib_crypt_salt" maxlength="16" value="<?php echo $core->__autoConfigDB('lib_crypt_salt')===false?$core->library->crypt->salt:$core->__autoConfigDB('lib_crypt_salt') ?>" />
		</td>
		<td>
			<?php echo $lang->get('salt'); ?> (16 <?php echo $lang->get('char'); ?>)
		</td>
	</tr>
</table><br />
<input type="submit" name="optSave" value="<?php echo $lang->get('saveoption'); ?>" />
</form>