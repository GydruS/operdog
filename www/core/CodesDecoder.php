<?php

class CodesDecoder {
    public $codes = Array();
    protected $locale = '';
    protected $supportedLocales = array('en', 'ru');
    
    public function __construct($requestedLocale = 'ru') {
        //if (!$this->setLocale($requestedLocale)) throw new Exception("Unsupported locale");
        $this->setLocale($requestedLocale);
    }

    public function addCode($code, $name, $description = '', $locale = null){
        if ($locale === null) $locale = $this->locale;
        elseif (!$this->isLocaleSupported($locale)) return false;
        $this->codes[$locale][$code]['name'] = $name;
        $this->codes[$locale][$code]['description'] = $description;
        return true;
    }
    
    public function getMessage($code){
        return $this->getField($code, 'name');
    }
    
    public function getDescription($code){
        return $this->getField($code, 'description');
    }
    
    public function getField($code, $field, $defaulValue = ''){
        if (!empty($this->codes[$this->locale][$code][$field])) {
            return $this->codes[$this->locale][$code][$field];
        }
        else return $defaulValue;
    }
    
    public function getSupportedLocales() {
        return $this->supportedLocales;
    }
    
    public function isLocaleSupported($locale) {
        return in_array($locale, $this->getSupportedLocales());
    }
    
    public function getLocale() {
        return $this->locale;
    }

    public function setLocale($locale) {
        if (!$this->isLocaleSupported($locale)) return false;
        $this->locale = $locale;
        return true;
    }
}