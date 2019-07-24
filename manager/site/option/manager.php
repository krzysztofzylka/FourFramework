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
	
	if(isset($_POST['show_service_menu']))
		optionWrite('show_service_menu', '1');
	else
		optionWrite('show_service_menu', '0');
	
	optionWrite('language', $_POST['language']);
	echo '<div class="message green">'.$lang->get('successsavemoduleoption').'</div>';
}
?>
<h1><?php echo $lang->get('manageroption') ?></h1>
<br />
<form method="POST">
<b><?php echo $lang->get('module') ?></b><br />
<table>
	<tr>
		<td>
			<select name="language">
				<option value="pl" <?php echo optionRead('language')=='pl'?'selected':'' ?>>Polski</option>
				<option value="en" <?php echo optionRead('language')=='en'?'selected':'' ?>>English</option>
			</select>
		</td>
		<td>
			<?php echo $lang->get('managerlang') ?>
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" name="module_show_dev_allConfig" <?php echo optionRead('module_show_dev_allConfig')==1?'checked':'' ?> />
		</td>
		<td>
			<?php echo $lang->get('devmodopt') ?> <b>(<?php echo $lang->get('dev') ?>)</b>
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" name="module_show_dev_allvardump" <?php echo optionRead('module_show_dev_allvardump')==1?'checked':'' ?> />
		</td>
		<td>
			<?php echo $lang->get('devvardump') ?> <b>(<?php echo $lang->get('dev') ?>)</b>
		</td>
	</tr>
	<tr>
		<td>
			<input type="checkbox" name="show_service_menu" <?php echo optionRead('show_service_menu')==1?'checked':'' ?> />
		</td>
		<td>
			<?php echo $lang->get('devservicemenu') ?> <b>(<?php echo $lang->get('dev') ?>)</b>
		</td>
	</tr>
</table>
<input type="submit" name="option_module" value="<?php echo $lang->get('saveoption') ?>" />
</form>