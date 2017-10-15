<?php

class ErrorsDecoder extends CodesDecoder {
    
//    public $ruCodes = Array(
//    );
    
    public function __construct($requestedLocale = 'ru') {
        parent::__construct($requestedLocale);
//        $this->codes = Array(
//            "ru" => &$this->ruCodes,
//            "en" => &$this->enCodes
//        );
        
        $locale = 'ru';
        $this->addCode(ERR_UNABLE_TO_LOAD_MODULE,   'Невозможно загрузить модуль', '', $locale);
        $this->addCode(ERR_MODULE_IS_NULL,          'Модуль не существует', '', $locale);
        $locale = 'en';
        $this->addCode(ERR_UNABLE_TO_LOAD_MODULE,   'Unable to load module', '', $locale);
        $this->addCode(ERR_MODULE_IS_NULL,          'Module is null', '', $locale);
    }

}