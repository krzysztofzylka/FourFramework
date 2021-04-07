<?php
return $this->form = new class(){
	public $version = '1.2';
	private $jqueryPOSTScript = false;

	//TODO: przerobić tą funkcję
	public function jqueryPOSTScript() {
		core::setError();

		if ($this->jqueryPOSTScript === true) {
			return false;
		}

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

	public function protectAllData($method) : bool { 
		core::setError();

		$method = strtoupper($method);

		if (!is_bool(array_search($method, ['GET', 'POST']))) {
			return core::setError(1, 'method not found');
		}

		switch ($method) {
			case 'GET':
				foreach($_GET as $key => $value){
					$_GET[$key] = htmlspecialchars($value);
				}
				break;
			case 'POST':
				foreach($_POST as $key => $value){
					$_POST[$key] = htmlspecialchars($value);
				}
				break;
		}

		return true;
	}
}; 
?>