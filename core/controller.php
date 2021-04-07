<?php
class core_controller{
    public function loadModel(string $modelName){
        $this->{$modelName} = core::loadModel(
            $modelName,
            [
                'returnOnly' => true
            ]
        );
    }
    public function loadView(string $viewName){
        $viewPath = core::loadView(
            $viewName,
            [
                'returnPathOnly' => true
            ]
        );
        return new view($viewPath);
    }
}

return new class(){
    public $_lastLoadController;
    public $_list = [];
}
?>