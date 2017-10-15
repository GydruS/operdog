<?php
#################################
#       GydruS's Engine 3       #
#       "CoreModule" class      #
#             v. 0.2            #
#           2017 03 26          #
#################################

#################################
# Description
#--------------------------------
# "Private" class for acessing/processing modules in Core
#

class CoreModule
{
    public $name = '';
    public $path = '';
    public $modulesPath = '';
    public $runCounter = 0;
    public $moduleObject = NULL;
    public $srcType = MODULE_TYPE_PP;
    public $selfRendering = FALSE;
    public $provideStyles = FALSE;
    public $provideScripts = FALSE;
    public $provideContext = FALSE;
    public $templateEngine = 'php';
    public $templateFile = NULL;
    public $outputMode = MODULE_OUTPUT_NORMAL;
    public $header = 'Content-type: text/html';
    public $data = Array();
    public $result = NULL;
    public $styles = Array();
    public $scripts = Array();
    public $allowedToActAsAPI = FALSE;
    protected $runInCore = TRUE;
    private $core = NULL;
    public $constructMicrotime = 0;
    public $runMicrotime = 0;
    public $renderMicrotime = 0;
    public $profilingInfo = Array();
    
    public function __construct($core, $name, $path, $allowedToActAsAPI = false) {
		$this->constructMicrotime = microtime(true);
        $this->core = $core;
        $this->templateEngine = $this->core->defaultTemplateEngine;
        $this->name = $name;
        $this->path = $path;
        $this->allowedToActAsAPI = $allowedToActAsAPI;
	}
	
    public function isModuleInstanceCreated() {
        return !empty($this->moduleObject);
    }
    
    public function getModuleInstance() {
        return $this->moduleObject;
    }
    
    public function createModuleInstance() {
        # When Module is OOP-styled
		if (class_exists ($this->name)) {
            $this->srcType = MODULE_TYPE_OOP;
			$this->moduleObject = new $this->name();
		}
        # When Module is Procedure-styled
		else {
        }
        
        $this->updateModuleInfo();
        return $this->moduleObject;
    }
    
    public function updateModuleInfo() {
        switch ($this->srcType) {
            case MODULE_TYPE_OOP:
                if (!empty($this->moduleObject)) {
                    $moduleObject = $this->moduleObject;
                    
                    if (!empty($moduleObject->outputMode)) $this->outputMode = $moduleObject->outputMode;
                    if (!empty($moduleObject->selfRendering)) $this->selfRendering = $moduleObject->selfRendering;
                    if (!empty($moduleObject->runInCore)) $this->runInCore = $moduleObject->runInCore;
                    ###
                    if (!empty($moduleObject->provideStyles)) $this->provideStyles = $moduleObject->provideStyles;
                    if (!empty($moduleObject->provideScripts)) $this->provideScripts = $moduleObject->provideScripts;
                    if (!empty($moduleObject->provideContext)) $this->provideContext = $moduleObject->provideContext;
                    ###
                    if (!$this->selfRendering) {
                        if (!empty($moduleObject->templateFile)) $this->templateFile = $moduleObject->templateFile;
                        if (!empty($moduleObject->templateEngine)) $this->templateEngine = $moduleObject->templateEngine;
                    }
                }
            break;
            case MODULE_TYPE_PP:
            break;
        }
    }
    
    public function allowedRunByCore() {
        if ($this->srcType == MODULE_TYPE_OOP) $this->runInCore = $this->moduleObject->runInCore;
        return $this->runInCore;
    }
    
    public function run($params, $allowJustFirstCall = TRUE) {
        if (($this->runCounter == 0) || !$allowJustFirstCall) {
			$beginMicrotime = microtime(true);
            $this->runCounter++;
            if ($this->srcType == MODULE_TYPE_OOP) {
				$actAsApi = $this->allowedToActAsAPI && $this->moduleObject->autoprocessJsonRequests && ($this->core->request->jsonOutput);
				if ($actAsApi) {
					$this->moduleObject->setOutputFormat('json');
                    $this->templateEngine = $this->moduleObject->templateEngine;
					$this->moduleObject->provideStyles = false;
					$this->moduleObject->provideScripts = false;
					$this->moduleObject->provideContext = false;
					$this->data = $this->moduleObject->actAsApi($params);
				}
				else {
			        $this->data = $this->moduleObject->getData($params);
		            if ($this->provideStyles) $this->styles = $this->moduleObject->getStyles();
	                if ($this->provideScripts) $this->scripts = $this->moduleObject->getScripts();
				}
            }
            $this->updateModuleInfo(); # обновляем run-time module info
			$beginRenderMicrotime = microtime(true);
            $result = $this->renderModule(); # render module
			$endMicrotime = microtime(true);
			$this->renderMicrotime = $endMicrotime - $beginRenderMicrotime;
			$this->runMicrotime = $endMicrotime - $beginMicrotime;
            $this->profilingInfo[$this->runCounter] = Array(
                'runCounter' => $this->runCounter,
                'params' => $params,
                'renderMicrotime' => $this->renderMicrotime,
                'runMicrotime' => $this->runMicrotime,
            );
        }
        else {
            $result = $this->result;
        }
        return $result;
    }
    
    private function renderModule() {
        if ($this->selfRendering) {
            switch ($this->srcType) {
                case MODULE_TYPE_OOP:
                    if (!empty($this->moduleObject->render)) $this->result = $this->moduleObject->render();
                break;
                case MODULE_TYPE_PP:
                    //$procName = $moduleName.'Render';
                    //if (is_callable($procName)) $result = $procName();
                break;
            }
        }
        else {
            $this->result = $this->applyTemplate();
        }
        return $this->result;
    }
    
    private function applyTemplate() {
        //TODO modulesPath -> templatesPath ?
		$path = $this->core->modulesPath.$this->path;
		$templateFile = (!empty($this->templateFile)) ? $this->templateFile : $path.$this->name.$this->getTemplateFileExtension($this->templateEngine);
        
        if (!empty($this->moduleObject)) {
            if ($this->moduleObject->provideContext) $this->data['context'] = $this->moduleObject->getContext();
            if ($this->moduleObject->provideScripts) $this->data['scripts']['script'] = $this->scripts;
            if ($this->moduleObject->provideStyles) $this->data['styles']['style'] = $this->styles;
        }
        else {
            if ($this->provideContext) $this->data['context'] = $this->core->getContext();
            if ($this->provideScripts) $this->data['scripts']['script'] = $this->scripts;
            if ($this->provideStyles) $this->data['styles']['style'] = $this->styles;
        }
        
		$view = new View($this->templateEngine, array(&$this->core, 'addError'));
		$this->header = $view->getHeader();
		return $view->render($this->data, $templateFile);
	}
    
    private function getTemplateFileExtension($templateEngine) {
		switch ($templateEngine) {
			case 'php': $templateExtension = '.view.php'; break;
			case 'xslt': $templateExtension = '.xsl'; break;
			default : $templateExtension = '.'.$templateEngine;
		}
		return $templateExtension;
	}
    
    public function getAsArray() {
        $data = Array();
        $data['name'] = $this->name;
        $data['path'] = $this->path;
        $data['runCounter'] = $this->runCounter;
        $data['moduleObject'] = $this->moduleObject;
        $data['outputMode'] = $this->outputMode;
        $data['header'] = $this->header;
        
        $data['moduleInfo']['selfRendering'] = $this->selfRendering;
        $data['moduleInfo']['srcType'] = $this->srcType;
        $data['moduleInfo']['provideStyles'] = $this->provideStyles;
        $data['moduleInfo']['provideScripts'] = $this->provideScripts;
        $data['moduleInfo']['provideContext'] = $this->provideContext;
        $data['moduleInfo']['templateEngine'] = $this->templateEngine;
        #$data['moduleInfo']['templateFile'] = $this->templateFile;
        
        $data['data'] = $this->data;
        $data['result'] = $this->result;
        return $data;
    }
    
}