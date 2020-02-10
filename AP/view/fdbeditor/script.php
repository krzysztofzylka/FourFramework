<?php
if(isset($_POST['script'])){
	$script = $_POST['script'];
	echo "<div class='card-header'>
		Wykonany skrypt
	</div>
	<div class='card-body'>
		<b>Komenda:</b><br />".$script.'<br /><b>Zwrócone dane:</b><br /><pre>';
		$request = core::$library->db->request($script);
		if(core::$error[0] > -1)
			echo 'Błąd numer: '.core::$error[0].PHP_EOL.'Nazwa: '.core::$error[1].PHP_EOL.'Opis: '.core::$error[2];
		else
			var_dump($request);
	echo "</pre></div>";
}
?>
<div class='card-header'>
	Wykonanie skryptu
</div>
<div class='card-body'>
	<form method='post'>
		<div class="form-group">
			<label>Skrypt</label>
			<textarea name='script' class='form-control'></textarea>
		</div>
		<button type="submit" class="btn btn-primary">Wykonaj skrypt</button>
	</form>
</div>