<?php
return new class($this){
	public $active = true;
	//rozpoczęcie pobierania czasu
	public function timeStart(){
		//jeżeli testowanie kodu wyłączone
		if($this->active == false) return;
		return microtime();
	}
	//wyświetlanie czasu ładowania kodu
	public function timeShow($time){
		//jeżeli testowanie kodu wyłączone
		if($this->active == false) return;
		//pobieranie i konwenterowanie czasu
		$end = microtime();
		$time = explode(' ', $time);
		$end = explode(' ', $end);
		$times = ($end[0]+$end[1])-($time[0]+$time[1]);
		//wyświetlanie czasu
		echo 'Skrypt wykonał się w <b>'.round($times, 4).'</b> sekundy.';
	}
	//debug
	public function __debugInfo(){
		return [
			'active' => $this->active?'true':'false',
		];
	}
}
?>