<?php
//nazwa kontrolera taka sama jak nazwa pliku/funkcji
class index{
	//funkcja główna
	public function __construct($core){
		//ładowanie widoku o nazwie index
		$core->loadView('index');
	}
}