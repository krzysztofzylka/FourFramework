<?php
class view{
    private $__viewTypeList = [
        'prepend' => [],
        'append' => []
    ];
    private $__viewGuiHelper = null;
    private $__viewType = null;
    private $__viewOption = [];
    private $__viewPath;
    private $__viewVariable = [];
    private $__controllerObject = null;

    public function __construct($__viewPath, $__viewOption=[]){
        core::setError();
        $this->__viewPath = $__viewPath;
        $this->__viewOption = $__viewOption;
        $this->_loadConfiguration();

		if (isset($__viewOption['controllerObject'])
			&& is_object($__viewOption['controllerObject'])
		) {
			$this->__controllerObject = $__viewOption['controllerObject'];
			$this->__viewVariable =  $__viewOption['controllerObject']->__view['variable'];
			$this->__viewType =  $__viewOption['controllerObject']->__view['viewType']??null;
		}

		foreach ($this->__viewVariable as $__variableName => $__variableValue) {
			${$__variableName} = $__variableValue;
		}
		if (!is_null($this->__viewGuiHelper)) {
			$this->{$this->__viewGuiHelper} = core::loadModel(
				$this->__viewGuiHelper,
				[
					'returnOnly' => true
				]
			);
		}

		ob_start();
		echo $this->_loadViewType('prepend');
		include($this->__viewPath);
		echo $this->_loadViewType('append');
		$__viewData = ob_get_contents();
		ob_get_clean();
		echo $__viewData;
    }
    private function _loadViewType($type){
		if($this->__viewType <> null and isset($this->__viewTypeList[$type][$this->__viewType])){
			$string = $this->__viewTypeList[$type][$this->__viewType];
			$string = preg_replace_callback('/{\$([a-zA-Z_\-.0-9]*)([|])?(.*?)}/sm', function ($matches) {
				switch (true) {
					case isset($this->__viewVariable[$matches[1]]):
						return $this->__viewVariable[$matches[1]];
						break;
					case $matches[2] === '|':
						return $matches[3];
						break;
					default:
						switch ($matches[1]) {
							case '__randomGuiId':
								$this->__viewVariable[$matches[1]] = md5(uniqid(rand(), true));
								return $this->__viewVariable[$matches[1]];
								break;
						}

						return $matches[0];
						break;
				}
			}, $string);
			return $string;
		}
		return '';
	}
    private function _loadConfiguration(){
        $configurationPath = core::$path['configuration'].'view.php';
        if (file_exists($configurationPath)) {
            $configuration =  include($configurationPath);

            if (isset($configuration['viewType'])) {
                $this->__viewTypeList = $configuration['viewType'];
            }

            if (isset($configuration['GuiHelperModel'])) {
                $this->__viewGuiHelper = $configuration['GuiHelperModel'];
            }
        }
    }
}
?>