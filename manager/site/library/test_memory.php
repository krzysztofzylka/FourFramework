<?php
if(isset($_POST['run'])){
	$library = 'memory';
	$function = htmlspecialchars($_POST['function']);
	echo '<div class="message">'.$lang->get('return').'<hr />';
	$var = '';
	for($x=1; $x<=10; $x++){
		if(isset($_POST['var'.$x])){
			if($var <> '')
				$var .= ', ';
			$vars = htmlspecialchars($_POST['var'.$x]);
			$var .= '"'.$vars.'"';
		}
	}
	var_dump(eval('return $core->library->'.$library.'->'.$function.'('.$var.');'));
	echo '</div>';
}
?>
<h1><?php echo $lang->get('librarytest') ?>: memory</h1>
<?php
$data = [
	'formatBytes' => [
		'$bytes',
		'$prec'
	],
];
foreach($data as $name => $var){
	echo '<div class="data">
	<h1>'.$lang->get('testfunction').': '.$name.'</h1>
	<form method="POST">
		<input type="hidden" name="function" value="'.$name.'" />
			<table>
				<tr>
					<td align="right">'.$name.'(</td>';
					$x = 1;
					foreach($var as $item){
						echo '<td><input type="text" name="var'.$x.'" placeholder="'.$item.'" /></td>';
						$x++;
					}
					echo '<td>)</td>
				</tr>
				<tr>
					<td><input type="submit" name="run" value="'.$lang->get('run').'" /></td>
				</tr>
			</table>
	</form>
	</div>';
} ?>