<?php
if(isset($_POST['login']) and isset($_POST['password'])){
	if(!isset($config['login'])) $config['login'] = 6;
	if(!isset($config['password'])) $config['password'] = 8;
	if((strlen($_POST['login']) < $config['login']) or (strlen($_POST['password']) < $config['password'])){
		if(isset($config['error2'])){
			echo $config['error2'];
		}else{
			echo "<b>Błędne dane logowania</b><br />";
		}
	}else{
		$register = $this->register($_POST['login'], md5($_POST['password']));
		if($register == true){
			if(isset($config['success_register'])){
				header('location: '.$config['success_register']);
			}else{
				header("Refresh:0");
			}
		}else{
			if(isset($config['error'])){
				echo $config['error'];
			}else{
				echo "<b>Błąd rejestracji</b><br />";
			}
		}
	}
}
?>
<form method='POST'>
	<?php if(isset($config['name']) && $config['name'] == true) echo 'Login<br />'  ?>
	<input type='text' name='login' <?php if(isset($config['placeholder']) && $config['placeholder'] == true) echo "placeholder='Login'"  ?> /><br />
	<?php if(isset($config['name']) && $config['name'] == true) echo 'Hasło<br />'  ?>
	<input type='password' name='password' <?php if(isset($config['placeholder']) && $config['placeholder'] == true) echo "placeholder='Hasło'"  ?> /><br />
	<input type='submit' value='Zerejestruj'>
</form>