<?php
return new class(){ //create main class
	public $hashAlghoritm = 'pbkdf2'; //hash alghoritm
	public $sessionName = 'userID'; //user session name
	public $userData = null; //user data
	public function __construct(){ //main function
		if(core::$library->database->isConnect === false)// if not connect
			return core::setError(1, 'no connection to the database'); //return error 1
	}
	public function _install(){ //install module to database
		$sql = "CREATE TABLE user (
			id INT(11) AUTO_INCREMENT PRIMARY KEY,
			login VARCHAR(30) NOT NULL,
			password VARCHAR(50) NOT NULL,
			email VARCHAR(50)
		);"; //sql
		try{
			core::$library->database->conn->exec($sql); //exec
			return true; //return success
		}catch(Exception $error){
			return core::setError(1, 'error create table', $error->getMessage()); //return error 1
		}
	}
	public function createUser(string $login, string $password, string $email){ //add user to database
		$password = core::$library->crypt->hash(htmlspecialchars($password), $this->hashAlghoritm); //crypt password
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) //check email
			return core::setError(1, 'Invalid email format'); //return error 1
		$exec = "INSERT INTO user (login, password, email) VALUES ('".htmlspecialchars($login)."', '".$password."', '".htmlspecialchars($email)."')"; //sql
		$check = core::$library->database->conn->query('SELECT count(*) as count FROM user WHERE login="'.htmlspecialchars($login).'" LIMIT 1')->fetch(PDO::FETCH_ASSOC); //count user
		if($check['count'] > 0) //check user
			return core::setError(2, 'a user with such a login already exists'); //return error 2
		core::$library->database->conn->exec($exec); //exec script
		return true; //return success
	}
	public function loginUser(string $login, string $password){ //login user
		$login = htmlspecialchars($login); //protect login
		$password = htmlspecialchars($password); //protect password
		$user = core::$library->database->conn->query('SELECT count(id) as count, id, password FROM user WHERE login="'.$login.'"')->fetch(PDO::FETCH_ASSOC); //get user from database
		if($user['count'] == 0) //not found
			return core::setError(1, 'user with such login was not found'); //return error 1
		$check = core::$library->crypt->hashCheck($password, $user['password']); //check password
		if($check === false) //check password
			return core::setError(2, 'password incorrect'); //return error 2
		$_SESSION[$this->sessionName] = (int)$user['id']; //set session
		return true; //return success
	}
	public function checkUser(){ //check user login
		if(!isset($_SESSION[$this->sessionName])) //check is isset session
			return false; //return false
		elseif(!is_int($_SESSION[$this->sessionName])) //check int
			return false; //return false
		return true; //return success
	}
	public function logoutUser(){ //logout
		if($this->checkUser() === false) //check user
			return core::setError(1, 'the user is not logged in'); //return error 1
		unset($_SESSION[$this->sessionName]); //delete session
		$this->userData = null; //delete userData
	}
	public function userGetData(){ //get user data
		$userID = (int)htmlspecialchars($_SESSION[$this->sessionName]); //protect user ID
		$user = core::$library->database->conn->query("SELECT * FROM user WHERE id=".$userID); //get Data
		$user = $user->fetch(PDO::FETCH_ASSOC); //fetch data
		$this->userData = $user; //add data to userData
		return $user; //return user data
	}
}
?>