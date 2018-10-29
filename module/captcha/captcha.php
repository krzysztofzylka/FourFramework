<?php
//$_SESSION['captcha_string'] - zawartość captcha
//$_SESSION['captcha_img'] - ścieżka do pliku z captcha
//moduł dla captchy
class captcha{
	//folder dla plików tymczasowych
	private $temp_dir = null;
	//nazwa czcionki
	public $fonts = 'arial.ttf';
	//zmienna z folderem z czcionkami
	private $fonts_dir = null;
	//znaki wyświetlane w captcha
	private $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	public function __construct($obj, $config){
		//tworzenie ścieżki do danych tymczsowych
		$this->temp_dir = $config['path']."tmp/";
		//tworzenie ścieżki do danych tymczasowych
		$this->fonts_dir = $config['path']."fonts/";
	}
	//generowanie captcha
	public function captcha($lettercount=6){
		//sprawdzanie czy folder logów istnieje
		if(!file_exists($this->temp_dir)) mkdir($this->temp_dir, 0644, true);
		//wygenerowany ciąg
		$word = null;
		//ścieżka do pliku
		$img = $this->temp_dir.time().round(mt_rand(1111, 9999), 0).".png";
		//szerokość captcha
		$width = 60+($lettercount*30);
		//tworzenie obrazka
		$image = imagecreatetruecolor($width, 50);
		$background_color = imagecolorallocate($image, 255, 255, 255);
		imagefilledrectangle($image,0,0,$width,50,$background_color);
		$line_color = imagecolorallocate($image, 0,0,0); 
		//tworzenie lini
		for($i=0;$i<round(rand(2,15),0);$i++)
			imageline($image,0,rand()%50,$width,rand()%50,$line_color);
		$pixel_color = imagecolorallocate($image, 0,0,255);
		//tworzenie szumu
		for($i=0;$i<2500;$i++)
			imagesetpixel($image,rand()%$width,rand()%50,"000000");
		//ilość znaków w ciągu
		$len = strlen($this->letters);
		//losowanie pierwszego znaku
		$letter = $this->letters[rand(0, $len-1)];
		$text_color = imagecolorallocate($image, 0,0,0);
		//generowanie znaków
		for ($i=0;$i<$lettercount;$i++) {
			$letter = $this->letters[rand(0, $len-1)];
			imagettftext(
			  $image,
			  rand(17, 32),
			  rand(-30, 30),
			  15+(30*$i),
			  rand(29, 50),
			  $text_color,
			  $this->fonts_dir.$this->fonts,
			  $letter
			 );
			$word.=$letter;
		}
		//tworzenie sesji
		$_SESSION['captcha_string'] = $word;
		$_SESSION['captcha_img'] = $img;
		//zapisywanie obrazka
		imagepng($image, $img);
		//czyszczenie starych znaków
		$this->cleantmp();
	}
	//czyszczenie folderu tymczasowego z captcha
	private function cleantmp(){
		foreach(glob($this->temp_dir."*.*") as $fname){
			$explode = explode("/", $fname);
			$time = explode(".", $explode[count($explode)-1])[0];
			$time = time()-$time;
			if($time >= 50) unlink($fname);
		}
	}
}
?>