<?php
return new class($this, $config){
	private $core;
	private $config;
	private $db;
	public $sessionName = "user_id";
	public $sessionNameIP = "user_ip";
	public $hashAlgoritm = "pbkdf2";
	public $data = [
		'tab_name' => 'uzytkownicy',
		'col_id' => 'id',
		'col_login' => 'login',
		'col_pass' => 'haslo',
	];
	public $user;
	public function __construct($core, $config){
		$this->core = $core;
		$this->config = $config;
		$this->db = $core->library->database;
	}
	public function getUserData() : bool{
		if(!$this->check()) return false;
		$this->user = $this->db->query('SELECT *, count(*) as count FROM '.$this->data->tab_name.' WHERE '.$this->data->col_id.'='.$this->getUserID().' LIMIT 1')->fetch(PDO::FETCH_ASSOC);
		if($this->user['count'] == 0){
			$this->user = null;
			return false;
		}
		unset($this->user['count']);
		return true;
	}
	public function login($login, $password) : bool{
		if(!$this->db->is_connect)
			return $this->core->returnError(1, 'you must connect to database');
		$password = $this->core->library->crypt->exHash($password, $this->hashAlgoritm);
		$prep = $this->db->prepare('SELECT id, count(*) as count FROM '.$this->data->tab_name.' WHERE '.$this->data->col_login.'=:login and '.$this->data->col_pass.'=:haslo LIMIT 1');
		$prep->bindParam(':login', $login);
		$prep->bindParam(':haslo', $password);
		$prep->execute();
		$prep = $prep->fetch(PDO::FETCH_ASSOC);
		if($prep['count'] == 0)
			return $this->core->returnError(2, 'such an account does not exist');
		$this->createSession((int)$prep['id']);
		if($this->core->lastError['number'] == -1)
			return true;
		return $this->core->returnError(3, 'inside error');
	}
	public function register($login, $password) : bool{
		if(!$this->db->is_connect)
			return $this->core->returnError(1, 'you must connect to database');
		$prep = $this->db->prepare('SELECT count(*) as count FROM '.$this->data->tab_name.' WHERE '.$this->data->col_login.'=:login LIMIT 1');
		$prep->bindParam(':login', $login);
		$prep->execute();
		$prep = $prep->fetch(PDO::FETCH_ASSOC);
		if($prep['count'] > 0){
			return $this->core->returnError(2, 'account is already exists');
		}else{
			$password = $this->core->library->crypt->exHash($password, $this->hashAlgoritm);
			$prep = $this->db->prepare('INSERT INTO '.$this->data->tab_name.' ('.$this->data->col_login.', '.$this->data->col_pass.') VALUES (:login, :haslo)');
			$prep->bindParam(':login', $login);
			$prep->bindParam(':haslo', $password);
			$prep->execute();
			return true;
		}
	}
	public function logout() : bool{
		unset($_SESSION[$this->sessionName]);
		$this->user = null;
		return true;
	}
	public function check() : bool{
		if(!isset($_SESSION[$this->sessionName])) return false;
		if(!is_int($_SESSION[$this->sessionName])) return false;
		if($_SESSION[$this->sessionName] < 0) return false;
		if($_SESSION[$this->sessionNameIP] <> $this->core->library->network->getClientIP()) return false;
		return true;
	}
	private function createSession($id) : bool{
		if(!is_int($id)) return $this->core->returnError(1, 'id is not integer');
		if($id < 0) return $this->core->returnError(2, 'id must be greater than 0');
		$_SESSION[$this->sessionName] = (int)$id;
		$_SESSION[$this->sessionNameIP] = $this->core->library->network->getClientIP();
		return true;
	}
	public function changePassword(int $id, string $password) : bool{ //do zrobienia
		if(!$this->db->is_connect)
			return $this->core->returnError(1, 'you must connect to database');
		$password = $this->core->library->crypt->hash($password, $this->hashAlgoritm);
		$prep = $this->db->prepare('UPDATE '.$this->data->tab_name.' SET '.$this->data->col_pass.'=:haslo WHERE '.$this->data->col_id.'=:id');
		$prep->bindParam(':id', $id);
		$prep->bindParam(':haslo', $password);
		$prep->execute();
		$prep = $prep->fetch(PDO::FETCH_ASSOC);
		return true;
	}
	public function loginForm($config=null) : bool{
		echo '<form method="POST">';
			if(isset($config['name']) && $config['name'] == true){
				if(isset($config['name_login'])) echo $config['name_login'].'<br />';
				else echo 'Login: <br />';
			}
			echo '<input type="text" name="modacc_login" placeholder="';
			if(isset($config['placeholder']) && $config['placeholder'] == true){
				if(isset($config['placeholder_login'])) echo $config['placeholder_login'];
				else echo 'Login';
			}
			echo '" /><br />';
			if(isset($config['name']) && $config['name'] == true){
				if(isset($config['name_password'])) echo $config['name_password'].'<br />';
				else echo 'Hasło: <br />';
			}
			echo '<input type="password" name="modacc_password" placeholder="';
			if(isset($config['placeholder']) && $config['placeholder'] == true){
				if(isset($config['placeholder_password'])) echo $config['placeholder_password'];
				else echo 'Hasło';
			}
			echo '" /><br />
			<input type="submit" value="';
			if(isset($config['button'])) echo $config['button'];
			else echo 'Zaloguj';
			echo '" />
		</form>';
		if(isset($_POST['modacc_login']) and isset($_POST['modacc_password'])){
			return $this->login($_POST['modacc_login'], $_POST['modacc_password']);
		}
		return false;
	}
	public function getUserID() : int{
		if(!isset($_SESSION[$this->sessionName])){
			$this->core->returnError(1, 'session error');
			return -1;
		}return (int)$_SESSION[$this->sessionName];
	}
	public function registerForm($config=null) : bool{
		echo '<form method="POST">';
			if(isset($config['name']) && $config['name'] == true){
				if(isset($config['name_login'])) echo $config['name_login'].'<br />';
				else echo 'Login: <br />';
			}
			echo '<input type="text" name="modacc_login" placeholder="';
			if(isset($config['placeholder']) && $config['placeholder'] == true){
				if(isset($config['placeholder_login'])) echo $config['placeholder_login'];
				else echo 'Login';
			}
			echo '" /><br />';
			if(isset($config['name']) && $config['name'] == true){
				if(isset($config['name_password'])) echo $config['name_password'].'<br />';
				else echo 'Hasło: <br />';
			}
			echo '<input type="password" name="modacc_password" placeholder="';
			if(isset($config['placeholder']) && $config['placeholder'] == true){
				if(isset($config['placeholder_password'])) echo $config['placeholder_password'];
				else echo 'Hasło';
			}
			echo '" /><br />';
			if(isset($config['name']) && $config['name'] == true){
				if(isset($config['name_repassword'])) echo $config['name_repassword'].'<br />';
				else echo 'Powtórz hasło: <br />';
			}
			echo '<input type="password" name="modacc_repassword" placeholder="';
			if(isset($config['placeholder']) && $config['placeholder'] == true){
				if(isset($config['placeholder_repassword'])) echo $config['placeholder_repassword'];
				else echo 'Powtórz hasło';
			}
			echo '" /><br />
			<input type="submit" value="';
			if(isset($config['button'])) echo $config['button'];
			else echo 'Zaloguj';
			echo '" />
		</form>';
		if(isset($_POST['modacc_login']) and isset($_POST['modacc_password']) and isset($_POST['modacc_repassword'])){
			if($_POST['modacc_repassword'] <> $_POST['modacc_password']){
				return $this->core->returnError('4', 'passwords are incorrect');
			}else return $this->register($_POST['modacc_login'], $_POST['modacc_password']);
		}
		return false;
	}
}
?>