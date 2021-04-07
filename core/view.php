<?php
class view{
    protected $__Class__viewPath = null;
    protected $__Class__viewData = [];
    public function __construct($viewPath, $viewData=[]){
        $this->__Class__viewPath = $viewPath;
        $this->__Class__viewData = $viewData;
    }
    public function bind($name, $value){
        $this->__Class__viewData[$name] = $value;
    }
    public function execute(){
        foreach ($this->__Class__viewData as $name => $value) {
            ${$name} = $value;
        }
        return include($this->__Class__viewPath);
    }
}
?>