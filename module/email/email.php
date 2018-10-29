<?php
//moduł operujący na e-mailu
class email{
	private $zapytanie;
	private $data;
	//kodowanie czcionki
	public $charset = "iso-8859-2";
	//typ wysyłanych danych
	public $content_type = "text/html";
	//adres e-mail
	public $email;
	//nagłówek wysyłanego e-maila
	public $naglowek = "";
	//e-mail dla replayTo
	public $replyTo = null;
	//funkcja zmieniająca kodowanie znaków
	public function setCharset($charset="UTF-8"){
		$this->charset = $charset;
	}
	//funkcja tworząca nagłówek
	public function start(){
		//jeżeli nie skonfigurowano konta to zwrócenie błędu
		if($this->email == "") return false;
		//jeżeli wypełniono replayTo
		if(!is_null($this->replyTo)) $this->naglowek = "Reply-to: ".$this->replyTo." <".$this->replyTo.">".PHP_EOL;
		$this->naglowek .= "From: ".$this->email." <".$this->email.">".PHP_EOL;
		$this->naglowek .= "MIME-Version: 1.0".PHP_EOL;
		$this->naglowek .= "Content-type: ".$this->content_type."; charset=".$this->charset."".PHP_EOL;
		$this->naglowek .= "X-Mailer: PHP/".phpversion().PHP_EOL;
		return true;
	}
	//funkcja wysyłająca e-mail
	public function send_email($email, $title, $message){
		//jeżeli nagłówek jest pusty zwrócenie błędu
		if($this->naglowek == "") return false;
		//wysyłanie e-maila
		return @mail($email, $title, $message, $this->naglowek);
	}
	//sprawdzanie adresu e-mail pod wzglęcem e-mailu tymczasowego/jednorazowego
	public function disposable_check($email){
		//pobieranie danych z api
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "http://api.antideo.com/email/".$email,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_HTTPHEADER => array(
				"cache-control: no-cache"
			),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		$response = json_decode($response, true);
		return boolval($response['disposable']);
	}
}
?>