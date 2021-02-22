<?php
return new class(){ //create main class
	public $hashAlghoritm = 'pbkdf2'; //hash alghoritm
	public $sessionName = 'userID'; //user session name
	public $userData = null; //user data
	public $DBTablePrefix = ''; //prefix for table in database
	public $defaultPermissionGroup = 1; //default permission
	private $dbConn = null; //PDO Object
	private $dbTable = [
		'user' => 'user',
		'groupPermission' => 'groupPermission'
	];
	public function __construct(){
		core::setError();
		if(core::$library->database->isConnect === false)
			return core::setError(1, 'Connect error');
		$this->dbConn = core::$library->database->conn;
	}
	//install module
	public function _install() : bool {
		core::setError();
		$sql = "CREATE TABLE `".$this->dbTable['user']."` (
			id INT(11) AUTO_INCREMENT PRIMARY KEY,
			login VARCHAR(30) NOT NULL,
			password VARCHAR(50) NOT NULL,
			email VARCHAR(50),
			permission int(11)
		);
		CREATE TABLE `".$this->dbTable['groupPermission']."` (
			`id` INT(24) NOT NULL AUTO_INCREMENT, 
			`name` VARCHAR(255) NOT NULL, 
			`permission` TEXT NOT NULL, 
			PRIMARY KEY (`id`)
		);
		INSERT INTO `".$this->dbTable['groupPermission']."` (`id`, `name`, `permission`) VALUES (1, 'Administrator', '[\"all_granted\"]')"; //sql
		try{
			$this->dbConn->exec($sql);
			return true;
		}catch(Exception $error){
			return core::setError(1, 'SQL Error', $error->getMessage()); //return error 1
		}
	}
	public function createUser(string $login, string $password, string $email) : bool {
		core::setError();
		if($this->_userExists($login) > 0)
			return core::setError(2, 'A user with such a login already exists');
		$password = core::$library->crypt->hash(htmlspecialchars($password), $this->hashAlghoritm);
		if(!filter_var($email, FILTER_VALIDATE_EMAIL))
			return core::setError(1, 'Invalid email format');
		$this->dbConn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$prepare = $this->dbConn->prepare('INSERT INTO '.$this->dbTable['user'].' (login, password, email, permission) VALUES (:login, :password, :email, :permission)');
		$prepare->bindParam(':permission', $this->defaultPermissionGroup, PDO::PARAM_INT);
		$prepare->bindParam(':login', $login, PDO::PARAM_STR);
		$prepare->bindParam(':password', $password, PDO::PARAM_STR);
		$prepare->bindParam(':email', $email, PDO::PARAM_STR);
		if(@!$prepare->execute())
			return core::setError(3, 'SQL ERROR', $prepare->errorInfo());
		return true;
	}
	public function loginUser(string $login, string $password) : bool {
		core::setError();
		$user = $this->dbConn->prepare('SELECT count(id) as count, id, password FROM '.$this->dbTable['user'].' WHERE login=:login LIMIT 1');
		if(!$user->execute([':login' => $login]))
			return core::setError(3, 'SQL ERROR', $user->errorInfo());
		$user = $user->fetch(PDO::FETCH_ASSOC);
		if((int)$user['count'] == 0)
			return core::setError(1, 'user with such login was not found');
		if(!core::$library->crypt->hashCheck(htmlspecialchars($password), $user['password']))
			return core::setError(2, 'password incorrect');
		$_SESSION[$this->sessionName] = (int)$user['id'];
		return true;
	}
	public function changePassword(string $login, string $password, string $newPassword){
		core::setError();
		$prepare = $this->dbConn->prepare('SELECT id, password FROM '.$this->dbTable['user'].' WHERE login=:login');
		$prepare->bindParam(':login', $login, PDO::PARAM_STR);
		if(!$prepare->execute())
			return core::setError(3, 'SQL ERROR', $prepare->errorInfo());
		$user = $prepare->fetch(PDO::FETCH_ASSOC);
		if(!core::$library->crypt->hashCheck($password, $user['password']))
			return core::setError(2, 'Password incorrect');
		$newPassword = core::$library->crypt->hash(htmlspecialchars($newPassword), $this->hashAlghoritm);
		$prepare = $this->dbConn->prepare('UPDATE '.$this->dbTable['user'].' SET password=:password WHERE id=:id');
		$prepare->bindParam(':password', $newPassword, PDO::PARAM_STR);
		$prepare->bindParam(':id', $user['id'], PDO::PARAM_INT);
		if(@!$prepare->execute())
			return core::setError(3, 'SQL ERROR', $prepare->errorInfo());
		return true;
	}
	public function checkUser() : bool {
		core::setError();
		if(!isset($_SESSION[$this->sessionName]) or !is_int($_SESSION[$this->sessionName]))
			return false; //return false
		return true; //return success
	}
	public function logoutUser() : bool{ //logout
		core::setError();
		if($this->checkUser() === false) //check user
			return core::setError(1, 'The user is not logged in'); //return error 1
		unset($_SESSION[$this->sessionName]); //delete session
		$this->userData = null; //delete userData
		return true;
	}
	public function userGetData(){ //get user data
		core::setError();
		$userID = $this->_userID();
		$user = $this->dbConn->prepare('SELECT * FROM '.$this->dbTable['user'].' WHERE id=:userID');
		$user->bindParam(':userID', $userID, PDO::PARAM_INT);
		$user->execute();
		$user = $user->fetch(PDO::FETCH_ASSOC);
		$this->userData = $user;
		$this->getPermission();
		return $user;
	}
	public function getData(int $userID = -1){
		core::setError();
		$userID = $userID<>-1?(int)$userID:$this->_userID();
		$user = $this->dbConn->prepare('SELECT * FROM '.$this->dbTable['user'].' WHERE id=:userID');
		$user->bindParam(':userID', $userID, PDO::PARAM_INT);
		$user->execute();
		return $user->fetch(PDO::FETCH_ASSOC);
	}
	public function getPermission(){ //get user permission
		core::setError();
		if($this->checkUser() === false) //check user
			return core::setError(1, 'The user is not logged in'); //return error 1
		if($this->userData === null)
			$this->userGetData();
		if(isset($this->userData['userPermissionArray']) and is_array($this->userData['userPermissionArray']))
			return $this->userData['userPermissionArray'];
		$prepare = $this->dbConn->prepare('SELECT permission FROM '.$this->dbTable['groupPermission'].' WHERE id=:permissionID');
		$prepare->bindParam(':permissionID', $this->userData['permission'], PDO::PARAM_INT);
		$prepare->execute();
		return json_decode($prepare->fetch(PDO::FETCH_ASSOC)['permission']);
	}
	public function checkPermission(string $name) : bool{ //check permission
		core::setError();
		if($this->checkUser() === false) //check user
			return core::setError(1, 'The user is not logged in'); //return error 1
		if($this->userData === null)
			$this->userGetData();
		$permission = $this->getPermission();
		if(!is_bool(array_search('all_granted', $permission)))
			return true;
		return !is_bool(array_search($name, $permission));
	}
	public function setTablePrefix(string $prefix) : bool{ //set table prefix
		core::setError();
		if($this->DBTablePrefix == ''){
			$this->dbTable['user'] = $prefix.'_user';
			$this->dbTable['groupPermission'] = $prefix.'_groupPermission';
		}else{
			$this->dbTable['user'] =  $prefix.str_replace($this->DBTablePrefix.'_', '', $this->dbTable['user']);
			$this->dbTable['groupPermission'] =  $prefix.str_replace($this->DBTablePrefix.'_', '', $this->dbTable['groupPermission']);
		}
		$this->DBTablePrefix = $prefix.'_';
		return true;
	}
	public function userList(){
		core::setError();
		return core::$library->database->query('SELECT * FROM '.$this->dbTable['user'])->fetchAll();
	}
	public function getUserID(string $login){
		core::setError();
		$getData = core::$library->database->prepare('SELECT id FROM '.$this->dbTable['user'].' WHERE login=:login');
		$getData->bindParam(':login', $login, PDO::PARAM_STR);
		$getData->execute();
		return $getData->fetch(PDO::FETCH_ASSOC)['id'];
	}
	public function getPermissionName(int $permissionID){
		core::setError();
		$permission = core::$library->database->prepare('SELECT * FROM '.$this->dbTable['groupPermission'].' WHERE id=:id');
		$permission->bindParam(':id', $permissionID, PDO::PARAM_INT);
		$permission->execute();
		return $permission->fetch(PDO::FETCH_ASSOC)['name'];
	}
	public function getPermissionList(){
		core::setError();
		$permission = core::$library->database->prepare('SELECT * FROM '.$this->dbTable['groupPermission']);
		$permission->execute();
		return $permission->fetchAll(PDO::FETCH_ASSOC);
	}
	public function setUserPermission(int $userID, int $permissionGroupID) : bool{
		core::setError();
		$permission = core::$library->database->prepare('UPDATE '.$this->dbTable['user'].' SET permission=:permission WHERE id=:id');
		$permission->bindParam(':permission', $permissionGroupID, PDO::PARAM_INT);
		$permission->bindParam(':id', $userID, PDO::PARAM_INT);
		$permission->execute();
		return true;
	}
	public function getUserList(){
		core::setError();
		$getData = core::$library->database->prepare('SELECT * FROM '.$this->dbTable['user']);
		$getData->execute();
		return $getData->fetchAll(PDO::FETCH_ASSOC);
	}
	private function _userExists(string $login){
		core::setError();
		$prepare = core::$library->database->prepare('SELECT count(*) as count FROM '.$this->dbTable['user'].' WHERE login=:login LIMIT 1');
		$prepare->execute([':login' => $login]);
		return (int)$prepare->fetch(PDO::FETCH_ASSOC)['count'];
	}
	private function _userID(){
		core::setError();
		return (int)htmlspecialchars($_SESSION[$this->sessionName]);
	}
}
?>