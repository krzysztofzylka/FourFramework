<?php

class core_model{
    public function loadModel(string $modelName){
        $this->{str_replace('.', '_', $modelName)} = core::loadModel(
            $modelName,
            [
                'returnOnly' => true
            ]
        );
    }
}
return new class(){
    public $_list = [];
}
?>