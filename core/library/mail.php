<?php
return $this->mail = new class($this->core){
	protected $core;
	public $version = '1.0';
	public $headers = [
		'From' => '',
		'Reply-To' => '',
		'X-Mailer' => null,
		'MIME-Version' => '1.0',
		'Content-type' => 'text/plain; charset=utf-8',
	];
	public function __construct($obj){
		$this->core = $obj;
		$this->headers['X-Mailer'] = 'FourFramework/'.$obj->version;
	}
	public function checkEmail(string $email) : bool{
		if(!filter_var($email, FILTER_VALIDATE_EMAIL))
			return false;
		list($user, $host) = explode("@", $email);
		if (!checkdnsrr($host, "MX") && !checkdnsrr($host, "A"))
			return false;
		return true;
	}
	public function setHeader(string $from, string $replayTo) : void{
		$this->headers = array(
			'From' => $from,
			'Reply-To' => $replayTo
		);
		return;
	}
	public function sendMail(string $to, string $subject, string $message) : bool{
		return mail($to, $subject, $message, $this->headers);
	}
};
?>