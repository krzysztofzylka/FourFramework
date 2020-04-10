<?php
return new class($config){
	private $config;
	private $passwordCryptAlg;
	public function __construct($config){
		$this->config = $config;
		$this->passwordCryptAlg = 'sha256';
		$password = core::$library->crypt->hash(htmlspecialchars(basename($config['dbPassword'])), $this->passwordCryptAlg); //generowanie nowego hasła
		file_put_contents($this->config['dbPath'].'passwd.php', "<?php return '".$password."'; ?>"); //podmiana pliku z hasłem
		foreach(array_diff(scandir($this->config['dbPath']), ['.' , '..', 'passwd.php']) as $tableName){ //pętla tabel
			$tableName = str_replace('.fdb', '', $tableName);
			$readTable = $this->readOldFile($tableName);
			$newTable = [ //główna tablica (zawiera wszystkie dane tabeli
				'option' => [ //opcje kolumny, nie szyfrowane
					'name' => $tableName, //nazwa tabeli
					'version' => '1.2', //wersja tabeli
					'columnCount' => '',//count($tableColumn), //ilość kolumny
					'dataCount' => 0, //ilość danych w tabeli
					'autoincrement' => [ //autoodliczanie (szyfrowane)
						'ai' => false, //czy autoodliczanie aktywne
						'colName' => null, //nazwa kolumny do autoodliczania
						'count' => 1 //licznik autoodliczania
					]
				],
				'column' => [], //kolumny
				'data' => $readTable['data'] //tablica na wszystkie dane w tabeli
			];
			//tworzenie kolumn
			foreach($readTable['column'] as $id => $item){
				if(!is_array($item)) continue;
				array_push($newTable['column'], [
					'name' => $item['name'], //nazwa kolumny
					'type' => $item['type'], //typ danych dla kolumny
					'length' => $item['length'], //maksymalna ilość znaków w kolumnie
					'autoincrement' => $readTable['option']['autoincrement']['ai']===true?($readTable['option']['autoincrement']['id']===$id?true:false):false, //autoodliczanie kolumny
					'defaultData' => null //domyślne dane dla kolumny
				]);
			}
			$newTable['option']['columnCount'] = count($newTable['column']); //zliczenie tabel
			$newTable['option']['dataCount'] = count($newTable['data']); //zliczenie danych
			//autoincrement
			$aiOld = $readTable['option']['autoincrement'];
			if($aiOld['ai'] === true){
				$newTable['option']['autoincrement']['ai'] = true;
				$newTable['option']['autoincrement']['count'] = $aiOld['count'];
				$newTable['option']['autoincrement']['colName'] = $newTable['column'][$aiOld['id']]['name'];
			}
			$newTable['option']['autoincrement'] = core::$library->crypt->crypt(json_encode($newTable['option']['autoincrement']), $password);
			$newTable['option'] = json_encode($newTable['option']);
			$newTable['column'] = core::$library->crypt->crypt(json_encode($newTable['column']), $password);
			$newTable['data'] = core::$library->crypt->crypt(json_encode($newTable['data']), $password);
			$tableText = $newTable['option'].PHP_EOL //utworzenie danych dla pliku .FDB
			.$newTable['column'].PHP_EOL
			.$newTable['data'];
			$tablePath = $this->config['dbPath'].$tableName.'.fdb';
			file_put_contents($tablePath, $tableText); //zapis tabeli
			// core::$library->debug->print_r([$tableText, $tablePath]);
		}
		return true;
	}
	private function readOldFile(string $tableName){
		core::setError();
		$readFile = file($this->config['dbPath'].$tableName.'.fdb');
		$returnData = [
			'option' => json_decode(trim($readFile[0]), true),
			'column' => json_decode($this->_decrypt(trim($readFile[1])), true),
			'data' => json_decode($this->_decrypt(trim($readFile[2])), true)
		];
		$returnData['option']['autoincrement'] = json_decode($this->_decrypt($returnData['option']['autoincrement']), true);
		return $returnData;
	}
	public function _decrypt($string){
		$password = md5($this->config['dbPassword']); //pobranie hasła
		return core::$library->crypt->decrypt($string, $password);
	}
}
?>