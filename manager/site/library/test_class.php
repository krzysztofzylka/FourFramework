<?php
if(isset($_POST['run'])){
	$library = 'class';
	echo '<div class="message">'.$lang->get('return').'<hr />';
	var_dump(eval('return $core->library->'.$library.'->is_anonymous("'.$_POST['var1'].'");'));
	echo '</div>';
}
?>
<h1><?php echo $lang->get('librarytest') ?>: class</h1>
<h2><?php echo $lang->get('testfunction') ?>: is_anonymous</h2>
<form method="POST">
	<table>
		<tr>
			<input type="hidden" name="function" value="is_anonymous" />
			<td align="right">is_anonymous(</td>
			<td><input type="text" name="var1" placeholder="$class" /></td>
			<td>) : bool</td>
		</tr>
		<tr>
			<td><input type="submit" name="run" value="<?php echo $lang->get('run') ?>" /></td>
		</tr>
	</table>
</form>