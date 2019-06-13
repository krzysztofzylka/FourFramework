<h1><?php echo $lang->get('lang') ?></h1>

<?php
if(isset($_POST['savelang'])){
	$listlang = ['en'];
	foreach($listlang as $item){
		$path = 'module/language/lang/lang-'.$item.'.php';
		$lang_data = "<?php return [";
		foreach($_POST as $name => $value){
			$pref = substr($name, 0, 3);
			if($pref == $item.'_'){
				$names = str_replace($item.'_', '', $name);
				$lang_data .= "'".$names."' => '".$value."', ";
			}
		}
		$lang_data .= "]; ?>";
		file_put_contents($path, $lang_data);
	}
	echo '<div class="message green">'.$lang->get('successsavelang').'</div>';
}
?>

<form method="POST">
<table class="title border">
	<tr>
		<td><?php echo $lang->get('name') ?></td>
		<td><?php echo $lang->get('lang') ?> pl</td>
		<td><?php echo $lang->get('lang') ?> en</td>
	</tr>
	<?php
	$lfilepl = include('module/language/lang/lang-pl.php');
	$lfileen = include('module/language/lang/lang-en.php');
	foreach($lfilepl as $name => $value){
		echo '<tr>
			<td>'.$name.'</td>
			<td><textarea rows="4" style="width: 200px;" disabled>'.$value.'</textarea></td>
			<td><textarea rows="4" style="width: 200px;" name="en_'.$name.'">'.@$lfileen[$name].'</textarea></td>
		</tr>';
	}
	?>
</table>
<br />
<input type="submit" value="<?php echo $lang->get('saveoption') ?>" name="savelang" />
</form>