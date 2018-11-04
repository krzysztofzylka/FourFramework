<?php
//moduł operujący na e-mailu
class email{
	//adres na który przesłany zostanie mail
	public $To = '';
	//nagłówki
	public $header = Array(
		'charset' => 'UTF-8', //kodowanie (iso-8859-2)
		'From' => '', //od kogo wiadomość
		'Reply-To' => '', //do kogo wiadomość
		'Content-type' => 'text/html', //typ wiadomości
		'MIME-Version' => '1.0', //wersja mime
	);
	//zmienna z wygenerowanym nagłówkiem
	private $generate_header;
	//funkcja generująca nagłówek
	public function init(){
		foreach($this->header as $name => $value){
			if($name == 'charset') continue;
			if($name == 'Content-type') $value .= '; charset='.$this->header['charset'];
			$this->generate_header .= $name.': '.$value.''.PHP_EOL;
		}
	}
	//funkcja wysyłająca e-mail
	public function send($title, $message){
		//anulacja jeżeli nagłówek nie został wygenerowanym
		if($this->generate_header == '') return false;
		//wysyłanie maila
		return @mail($this->To, $title, $message, $this->generate_header);
	}
	//sprawdzanie adresu e-mail pod wzglęcem e-mailu tymczasowego/jednorazowego
	public function disposable_check($email){
		// pobieranie danych z api
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
	//funkcja debugująca
	public function __debugInfo() {
        return [
			'To' => $this->To,
			'header' => $this->header,
			'generate_header' => $this->generate_header
		];
    }
}