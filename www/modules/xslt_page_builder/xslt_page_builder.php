<?php
class xslt_page_builder extends Module
{
    public $selfRendering = false;
    public $templateEngine = 'xslt';
    public $outputMode = MODULE_OUTPUT_NORMAL;
    public $queriedModule = '';
	public $settingsModule = null;
	
	public function __construct() {
		parent::__construct();
        $this->settingsModule = $this->core->loadModuleIfNotLoadedAndGetItsObject('settings');
	}
	
    public function getData($params = null) {
        if ($this->core->request->xmlOutput) {
            $this->templateEngine = 'xml';
            $this->outputMode = MODULE_OUTPUT_EXCLUSIVE;
        }
        $path = Array();
        $data['info']['siteName'] = $this->settingsModule->getSetting('siteName');
        $data['info']['siteURL'] = rtrim($this->core->request->getSiteURL(), '/');
        $data['counters'] = $this->settingsModule->getSetting('counters');
        $data['footer'] = $this->settingsModule->getSetting('footer');
        $auth = $this->core->getLoadedModule('auth');
        $data['user'] = $auth->data;
        $moduleName = (!empty($this->core->request->queriedStruct[0])) ? $this->core->request->queriedStruct[0] : 'adverts';
        $module = $this->core->getLoadedModule($moduleName);
        if ($module) {
            $this->queriedModule = &$module;
            $data['queriedModule'] = $this->queriedModule->name;
            $data['queriedModuleAction'] = isset($this->queriedModule->data['action']) ? $this->queriedModule->data['action'] : '';
            $content = $module->result;
        }
        else {
			$this->error('Запрошенная страница не найдена!');
			$content = '';
        }
        $data['queriedModuleContent'] = $content;
		
        return $data;
    }
    
    public function getScripts() {
        $scripts = array('/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/jquery.js');
		$scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/jquery.cookie.js';
		$scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/jquery.sticky.js';
		$scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/bootstrap/js/bootstrap.js';
        $scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/bootstrap/plugins/bootbox.min.js';
		$scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/lightbox/js/lightbox.js';
		$scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/plugins/datepicker/bootstrap-datepicker.js';
		$scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/plugins/datepicker/bootstrap-datepicker.ru.js';
		$scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/plugins/clockpicker/clockpicker.js';
		$scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/plugins/jquery.maskedinput.min.js';
        //$scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/oop.js';
        $scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/common.js';
        //$scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/AutoAPI.js';
        $scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'xslt_page_builder/xslt_page_builder.js';
        $scripts = array_merge($scripts, $this->core->getScripts());
        return $scripts;
    }
    
    public function getStyles() {
        $styles = $this->core->getStyles();
		$styles[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/bootstrap/css/bootstrap.css';
		$styles[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/lightbox/css/lightbox.css';
		$styles[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/css/plugins/datepicker/datepicker3.css';
		$styles[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/css/plugins/clockpicker/clockpicker.css';
        //$styles[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/font-awesome/css/font-awesome.min.css';
		$styles[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'xslt_page_builder/xslt_page_builder.css';
        return $styles;
    }
}
