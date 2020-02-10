<?php
return $this->form = new class(){ 
	public $version = '1.1'; 
	private $jqueryPOSTScript = false;
	public function list(string $method){ 
		core::setError(); 
		$method = strtoupper($method); 
		if(!$this->_methodCheck($method)) 
			return core::setError(1, 'method not found'); 
		switch($method){ 
			case 'GET': 
				return array_keys($_GET); 
				break;
			case 'POST': 
				return array_keys($_POST); 
				break;
		}
	}
	public function jqueryPOSTScript(){
		if($this->jqueryPOSTScript === true)
			return false;
		echo "<script>
		function UrlPostData(data){
			$('body').append('<form id=\"FFModUrlPostDataForm\" method=\"POST\"></form>');
			var exp = data.split(\"&\");
			exp.forEach(function(item) {
				var exp2 = item.split(\"=\", 2);
				$('#FFModUrlPostDataForm').append('<input type=\"hidden\" name=\"'+exp2[0]+'\" value=\"'+exp2[1]+'\">');
			});
			$('#FFModUrlPostDataForm').submit();
		}
		</script>";
		$this->jqueryPOSTScript = true;
		return true;
	}
	public function protectAllData($method) : bool{ 
		core::setError();
		$method = strtoupper($method); 
		if(!$this->_methodCheck($method)) 
			return core::setError(1, 'method not found'); 
		switch($method){ 
			case 'GET': 
				foreach($_GET as $key => $value) 
					$_GET[$key] = htmlspecialchars($value); 
				break;
			case 'POST': 
				foreach($_POST as $key => $value) 
					$_POST[$key] = htmlspecialchars($value); 
				break;
		}
		return true;
	}
	private function _methodCheck($name){ 
		core::setError();
		$method_list = ['GET', 'POST']; 
		return array_search($name, $method_list)>-1?true:false; 
	}
	
}; 
?>