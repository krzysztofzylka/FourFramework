<?php
if(isset($_POST['login']) and isset($_POST['password'])){
	$login = $this->login($_POST['login'], md5($_POST['password']));
	if($login == true){
		if(isset($config['success'])){
			header('location: '.$config['success']);
		}else{
			header("Refresh:0");
		}
	}else{
		if(isset($config['error'])){
			echo $config['error'];
		}else{
			echo "<b>Błąd logowania</b><br />";
		}
	}
}
?>
<form method='POST'>
	<?php if(isset($config['name']) && $config['name'] == true) echo 'Login<br />'  ?>
	<input type='text' name='login' <?php if(isset($config['placeholder']) && $config['placeholder'] == true) echo "placeholder='Login'"  ?> /><br />
	<?php if(isset($config['name']) && $config['name'] == true) echo 'Hasło<br />'  ?>
	<input type='password' name='password' <?php if(isset($config['placeholder']) && $config['placeholder'] == true) echo "placeholder='Hasło'"  ?> /><br />
	<input type='submit' value='Zaloguj'>
</form>