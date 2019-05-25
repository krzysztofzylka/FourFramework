<?php
if(isset($_POST['login'])){
	$check = $db->getData('manager_user', ['login='.$_POST['login'], 'password='.$core->library->crypt->exHash($_POST['haslo'])], false);
	if($check == false){
		echo '<div class="message red">
			Nie udało się zalogować
		</div>';
	}else{
		$_SESSION['userID'] = $check['id'];
		echo '<div class="message green">
			Poprawnie zalogowano, za chwilę powinno nastąpić przekierowanie na <a href="index.php">tą</a> stronę
		</div>';
		header('location: index.php');
	}
}
?>
<div class="logowanie">
	<h1>Logowanie</h1>
	<form method="POST">
		<label>Login</label><br />
		<input type="text" name="login" /><br />
		<label>Hasło</label><br />
		<input type="password" name="haslo" /><br />
		<input type="submit" value="Zaloguj" />
	</form>
</div>