<?php
#################################
#       GydruS's Engine 3       #
#         "Module" class        #
#             v. 1.0            #
#           2012 10 10          #
#################################

#################################
# Description
#--------------------------------
# Base Class For Application Modules
#

class Module
{
	public $templateEngine = 'php';
    public $templateFile = '';
	public $selfRendering = FALSE;
	public $outputMode = MODULE_OUTPUT_NONE; //MODULE_OUTPUT_NORMAL;
	public $provideStyles = TRUE;
	public $provideScripts = TRUE;
	public $provideContext = TRUE; //
	public $autoprocessJsonRequests = FALSE; //
    public $expandResult = true; // false - возвращать только результат метода; true - возвращать расширенные данные вместе с результатом (result, success, errors, etc);
    public $deniedAPIMethods = array('__construct', '__clone', '__call', 'actAsApi', 'tryMethod'); //Спсиок публичных методов, которые запрещены к вызову
    public $params = NULL;
    public $runInCore = true; // вызывать ли run() из ядра в runLoadedModules();
	protected $core;
	protected $db;
	protected $cache;
    protected $stop = FALSE;
    
    public function __construct() {
        global $core, $db, $cache;
        //if (!empty($core)) 
        $this->core = $core;
        $this->db = $db;
        $this->cache = $cache;
        
        #$this->templateFile = __DIR__.DS.get_class($this).'.xsl';//.$this->templateEngine;
        #var_dump($this->templateFile);
        #$this->templateFile = '';
	}
	
    public function actAsApi($params) {
		$result = null;
		$method = $this->core->request->getParam(0);
		
		if (!empty($method) && $this->isAvailibleForAPICall($method)) {
			$result = $this->tryMethod($method, false, $gotResult, $params);
		}
		else $this->error('Method denied!');
		
		if ($this->expandResult) $result = $this->expandResult($result);
		return $result;
	}
	
    public function expandResult($result) {
	    $result = array('success' => !$this->core->errorsCount(), 'result' => $result);
        if (!$result['success']) $result['errors'] = $this->core->getErrors();
        return $result;
    }
	
    protected function tryMethod($methodName, $defaultResult = false, &$gotResult = false, $params = array()) {
        $result = $defaultResult;
        $method = array($this, $methodName);
        if (is_callable($method)) {
            if (empty($params)) $params = array_slice(func_get_args(), 4);
            $result = call_user_func_array($method, $params);
            $gotResult = true;
        }
        return $result;
    }
	
    public function isAvailibleForAPICall($method) {
        $result = true;
        try {
            $reflection = new ReflectionMethod(get_class($this), $method);
            $result = $result && $reflection->isPublic();
        }
        catch (Exception $e) {
            $result = false;
        }
        return $result && !in_array($method, $this->deniedAPIMethods);
    }
	
    public function getCoreModuleInfoObject() {
        $coreModuleInfo = $this->core->getLoadedModule(get_class($this));
		return $coreModuleInfo;
	}
	
    public function getData($params = null) {
        $this->params = $params;
		return Array();
	}
	
    public function getScripts() {
		return Array();
	}
    
    public function getStyles() {
		return Array();
	}
	
    public function getContext() {
        $data = $this->core->getContext();
        $data['modulePath'] = $this->getPath();
        $data['moduleName'] = $this->getName();
		return $data;
	}
    
    # возвращает путь к папке с файлами модуля относительно корня веб-сервера
    public function getFullPathRelativeWebRoot() {
        return $this->core->getModulesPathRelativeWebRoot().$this->getPath();
	}
    
    public function getPath() {
        $coreModuleInfo = $this->getCoreModuleInfoObject();
        return $coreModuleInfo->path;
	}
    
    public function getName() {
        $coreModuleInfo = $this->getCoreModuleInfoObject();
        return $coreModuleInfo->name;
	}
    
    public function render() {
		return '';
	}
    
    protected function error($msg, $errorCode = '') {
        $this->stop = true;
        $this->core->addError($msg, $errorCode);
    }
    
    protected function notice($msg, $header = null) {
        $this->core->addNotice($msg, $header, $this->getName());
    }
    
    public function setOutputFormat($templateEngine, $outputMode = MODULE_OUTPUT_EXCLUSIVE) {
        $this->templateEngine = $templateEngine;
        $this->outputMode = $outputMode;
    }

}