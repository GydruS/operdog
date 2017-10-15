<?php

class demo extends Module {
    public $templateEngine = 'xslt';
    
    public function getData($params = null) {
        $data = array();
        $data['dynamicText'] = 'Some dynamic text '.rand(1, 999999).'...';
        return $data;
    }
}

?>
