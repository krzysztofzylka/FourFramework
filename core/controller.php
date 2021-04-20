<?php
class core_controller{
    public $__view = [
        'variable' => [],
        'viewType' => null,
    ];

    public function loadModel(string $modelName) {
        core::setError();

        $this->{str_replace('.', '_', $modelName)} = core::loadModel(
            $modelName,
            [
                'returnOnly' => true
            ]
        );
    }
    public function loadView(string $viewName) {
        core::setError();

        $viewPath = core::loadView(
            $viewName,
            [
                'returnPathOnly' => true
            ]
        );

        return new view(
            $viewPath,
            [
                'controllerObject' => $this
            ]
        );
    }

    public function viewSetVariable(string $variableName, $variableValue) {
        $this->__view['variable'][$variableName] = $variableValue;
    }
    public function viewSetType(string $typeName) {
        $this->__view['viewType'] = $typeName;
    }
}

return new class() {
    public $_lastLoadController;
    public $_list = [];
}
?>