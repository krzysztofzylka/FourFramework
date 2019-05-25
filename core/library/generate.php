<?php
return $this->generate = new class($this->core){
	protected $core;
	public $version = '1.0';
	public function __construct($obj){
		$this->core = $obj;
	}
	public function generateRandomArray(array $option, int $amount=1){
		if(!isset($option['data']))
			return $this->core->returnError(1, 'error option data', 'no data in array ("data")');
		if($amount < 1)
			return $this->core->returnError(2, 'error create array', 'data ammount is smaller than 1');
		$return = [];
		for($x=0; $x<$amount; $x++){
			$temp = [];
			foreach($option['data'] as $key => $data){
				if(is_array($data)){
					$gen = $this->core->library->string->generateString(isset($data['generateCount'])?$data['generateCount']:20, isset($data['generateType'])?$data['generateType']:[true, true, true, true]);
					if(isset($data['hash']))
						$gen = $this->core->library->crypt->exHash($gen, $data['hash']);
					$temp[$key] = $gen;
				}else
					$temp[$data] = $this->core->library->string->generateString(20);
			}
			array_push($return, $temp);
		}
		print_r($option);
		return $return;
	}
	public function uniqueID(bool $hash = false) : string{
		time_nanosleep(0, rand(5000, 10000));
		$return = '';
		$date = date('Ymdhms');
		$msec = substr(explode('.', explode(' ', microtime(false))[0])[1], 0, 6);
		$mtime = explode(' ', microtime(false))[1];
		$generate = $mtime.$msec.rand(100,999);
		if($hash)
			$generate = $this->core->library->crypt->hash($generate, 'md5');
		return (string) $generate;
	}
	public function createNoise(int $x=100, int $y=100){
		$return = [];
		if($x<=0)
			return $this->returnError(1, 'the x value must be greater than 0');
		if($y<=0)
			return $this->returnError(2, 'the y value must be greater than 0');
		for($yy=0;$yy<$y;$yy++){
			$array = [];
			for($xx=0;$xx<$x;$xx++){
				$array[$xx] = rand(0, 255);
			}
			$return[$yy] = $array;
		}
		return $return;
	}
}
?>