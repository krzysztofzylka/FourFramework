<?php
class test_g5hAGth{
	public $active = true;
	public function timeStart(){
		if($this->active == false) return;
		return microtime();
	}
	public function timeShow($time){
		if($this->active == false) return;
		$end = microtime();
		$time = explode(' ', $time);
		$end = explode(' ', $end);
		$times = ($end[0]+$end[1])-($time[0]+$time[1]);
		echo 'Skrypt wykonał się w <b>'.round($times, 4).'</b> sekundy.';
	}
	public function __debugInfo(){
		return [
			'active' => $this->active?'true':'false',
		];
	}
}