<?php
#################################
#       GydruS's Engine 3       #
#         "Core" class          #
#           v. 1.1.3            #
#    2012 10 09 - 2016 05 17    #
#################################

#################################
# Description
#--------------------------------
#
#

class Core
{
    public $request;
    public $loadedModules;
    public $modulesPath = DEFAULT_MODULES_PATH;
    public $siteRootPath = ''; // путь к корню обрабатываемого ядром сайта. Нужен в том случае, когда ядро и сайт и его модули находятся в разных местах и по сути только для того, чтобы отображение знало откуда брать файлы
    public $siteRootRelativePath = ''; // путь к корню обрабатываемого ядром сайта. Нужен в том случае, когда ядро и сайт и его модули находятся в разных местах и по сути только для того, чтобы отображение знало откуда брать файлы
    public $defaultModule = 'main';
    public $autoloadModules;
    public $finalizingModules;
    public $defaultTemplateEngine = 'php';
    public $logger;
    public $config;
    public $errorDecoder;
    public $constructMicrotime = 0;
    public $logsPath = '';
    public $contextExpander = array();
    public $loadedRegularModulesCount = 0;
    protected $modulesStorage = null; // хранилище информации о модулях приложений, которые загружаются и обрабатываются ядром
    protected $errors;
    protected $notices;
    protected $scripts = array();
    protected $styles = array();
    protected $finalized = false;
    protected $coreInfoString = 'GeThree v. 1.1.3';
 
    public function __construct($defaultModule = 'main', $siteRoot = '', $configPath = '', $logsPath = '') {
		$this->constructMicrotime = microtime(true);
		$this->defaultModule = $defaultModule;
		$this->request = Request::getInstance($siteRoot);
		$this->loadedModules = Array();
		$this->autoloadModules = Array();
		$this->finalizingModules = Array();
        $this->modulesStorage = new DataKeeper();
		$this->errors = new ErrorsKeeper();
		$this->notices = new NoticesKeeper();
		$this->config = $this->createConfig($configPath);
        $this->logsPath = $logsPath;
		$this->logger = $this->createLogger();
		$this->errorDecoder = new ErrorsDecoder();
        
        // modulesPath
        $this->modulesPath = $this->config->read('modulesFolderPath');
        if (empty($this->modulesPath)) $this->modulesPath = $this->config->read('modulesFolderRelativePath');
        if (empty($this->modulesPath)) $this->modulesPath = 'modules/';
        
        // siteRootPath
        $this->siteRootPath = $this->config->read('siteRootPath');
        $this->siteRootRelativePath = $this->config->read('siteRootRelativePath');
        
        // timeZone
        $this->setDefaultTimezone();
    }
    
    public function __destruct() {
        if (!$this->finalized) $this->finalize();
    }

    protected function finalize() {
        $this->config->finalize();
        $this->finalized = true;
        $this->logErrors();
    }
    
    protected function logErrors() {
        $message = '';
        $count = count($this->errors->errors);
        $i = 1;
        foreach ($this->errors->errors as $error) {
            $message .= date("Y-m-d H:i:s", $error['time'])."\t".$error['message'];
            if (!empty($error['code'])) $message .= "\t[Code:{$error['code']}]";
            if (!empty($error['description'])) $message .= "\t{$error['description']}";
            if ($i<$count) $message .= PHP_EOL;
            $i++;
        }
        if (!empty($message)) $this->logger->write($message, false);
    }
    
    public function setDefaultTimezone($timezone = null) {
        if (empty($timezone)) $timezone = $this->config->read('timezone', 'Europe/Moscow');
        $isTimezoneCorrect = in_array($timezone, DateTimeZone::listIdentifiers());
        if ($isTimezoneCorrect) {
            date_default_timezone_set($timezone);
        }
    }
    
    private function createConfig($configPath) {
        if (empty($configPath)) {
            $configRelativePath = ALLOW_EXTERNAL_CONFIGS ? $this->request->getConfigRelativePath() : '';
            if (empty($configRelativePath)) $configRelativePath = 'config.php';
            $configPath = $_SERVER['DOCUMENT_ROOT'].'/'.$this->request->siteRoot.$configRelativePath; // такое построение пути обусловленно тем, что относительный путь у экземпляра класса Config будет отличаться от относительного пути экземпляра Core, созданного в index.php (поэтому и юзаем полный путь)
        }
		return (new Config($configPath, array(&$this, 'configErrorCallback')));
    }
	
    public function getLogsPath() {
        if (!empty($this->logsPath)) $logsPath = $this->logsPath;
        else {
            if (!empty($this->config)) $logsPath = $this->config->read('logsPath');
            if (empty($logsPath))
                $logsPath = $_SERVER['DOCUMENT_ROOT'].'/'.$this->request->siteRoot.'/logs/'; // такое построение пути обусловленно тем, что относительный путь у экземпляра класса Logger будет отличаться от относительного пути экземпляра Core, созданного в index.php (поэтому и юзаем полный путь)
        }
        return $logsPath;
    }
    
    private function createLogger() {
        return (new Logger($this->getLogsPath()));
    }
	
    public function run() {
        $this->loadModules();
        $this->runLoadedModules();
        $this->finalize();
		$this->output();
    }
    
    private function loadModules() {
        $this->loadModulesFromArray($this->autoloadModules);
        
		$pathArr = Array();//$this->modulesPath);
        $modulesToLoad = Array();
        foreach ($this->request->queriedStruct as $moduleName) {
            $pathArr[] = $moduleName;
            $modulesToLoad[] = $this->buildModuleToLoadData(implode('/', $pathArr)/*, $moduleName*/);
        }
        if (!count($modulesToLoad)) $modulesToLoad = Array($this->defaultModule);
        $this->correctModulesToLoadArray($modulesToLoad);
		foreach ($modulesToLoad as $moduleData) {
            $res = $this->loadModule($moduleData['path'], $moduleData['name'], $this->modulesPath, true);
            if ($res === false) $this->request->parameters[] = $moduleData['name'];
            else $this->loadedRegularModulesCount++;
		}
        
        $this->loadModulesFromArray($this->finalizingModules);
    }
    
    public function runLoadedModules() {
		foreach ($this->modulesStorage->items as $coreModule) {
            if ($coreModule->allowedRunByCore()) $this->runModule($coreModule->name);
        }
    }
	
    public function getLoadedModules() {
		return $this->modulesStorage->items;
    }
    
    public function loadModulesFromArray(&$modulesToLoad) {
        $this->correctModulesToLoadArray($modulesToLoad);
		foreach ($modulesToLoad as $moduleData)
            $this->loadModule($moduleData['path'], $moduleData['name'], $this->modulesPath);
    }
    
    private function correctModulesToLoadArray(&$arr) {
        foreach ($arr as $key => $value) if (is_string($value)) $arr[$key] = $this->buildModuleToLoadData($value);
    }
	
    public function buildModuleToLoadData($path, $name = NULL) {
        $path = trim($path,'/');
        if (empty($name)) $name = geCSV_GetLastVal($path, '/');
        $moduleToLoadData = Array('path' => $path.'/', 'name' => $name);
        return $moduleToLoadData;
    }
	
    public function loadAndRunModule($path, $name, $modulesFolderPath = DEFAULT_MODULES_PATH, $params = null) {
		if ($this->loadModule($path, $name, $modulesFolderPath)) return $this->runModule($name, $params);
		else return FALSE;
	}
	
    private function runModule($name, $params = null) {
        $this->createModuleInstance($name);
        return $this->runLoadedModule($name, true, $params);
    }
    
    public function loadModule($path, $name, $modulesFolderPath = DEFAULT_MODULES_PATH, $allowedToActAsAPI = false) {
		if (Load::module($path, $name, $modulesFolderPath)) {
            $this->modulesStorage->add(new CoreModule($this, $name, $path, $allowedToActAsAPI), $name);
			return TRUE;
        }
		else {
            //$errorDescription = "path = '$path'; name = '$name'; modulesFolderPath = '$modulesFolderPath';";
            //$this->addError('Load module fails', ERR_UNABLE_TO_LOAD_MODULE, $errorDescription);
            return FALSE;
        }
	}
    
    public function loadModuleIfNotLoadedAndGetItsObject($name, $path = NULL, $modulesFolderPath = NULL, $runInCore = false) {
        if (!$this->isModuleLoaded($name)) {
            if ($path === NULL) $path = $name.'/';
            if ($modulesFolderPath === NULL) $modulesFolderPath = $this->modulesPath;
            if (!$this->loadModule($path, $name, $modulesFolderPath)) return false;
        }
        $loadedModule = $this->modulesStorage->get($name);
        if (empty($loadedModule->moduleObject)) {
            $this->createModuleInstance($name);
            $loadedModule->moduleObject->runInCore = $runInCore;
        }
        return $this->getLoadedModuleObject($name);
    }
    
    public function createModuleInstance($moduleName, $forceCreate = false) {
        $coreModule = $this->modulesStorage->get($moduleName);
        if ($forceCreate || !$coreModule->isModuleInstanceCreated()) $coreModule->createModuleInstance();
        return $coreModule->getModuleInstance();
    }

    public function runLoadedModule($moduleName, $allowJustFirstCall = true, $params = null) {
        $result = null;
        $coreModule = $this->modulesStorage->get($moduleName);
        if (!empty($coreModule)) $result = $coreModule->run($params, $allowJustFirstCall);
        else {
            //$this->addErrorByCode(ERR_MODULE_IS_NULL);
            $errorMessage = $this->errorDecoder->getMessage(ERR_MODULE_IS_NULL);
            $errorDescription = 'Модyль "'.$moduleName.'" не найден';
            $this->errors->addError($errorMessage, ERR_MODULE_IS_NULL, $errorDescription);
        }
        return $result;
	}
    
    public function addStyle($style) {
        $this->styles[] = $style;
    }
    
    public function addScript($script) {
        $this->scripts[] = $script;
    }
    
    # возвращает список стилей для УЖЕ загруженныХ модулей и тех что зарегистрированы дополнительно через addStyle();
    public function getStyles() {
        $styles = array();
        foreach ($this->modulesStorage->items as $coreModule) $styles = array_merge($styles, $coreModule->styles);
        return array_merge($this->styles, $styles);
    }
    
    # возвращает список скриптов для УЖЕ загруженныХ модулей
    public function getScripts() {
        $scripts = array();
        foreach ($this->modulesStorage->items as $coreModule) $scripts = array_merge($scripts, $coreModule->scripts);
        return array_merge($this->scripts, $scripts);
    }
	
    private function raw_output($data, $header = '') {
		if (!empty($header)) header($header);
        echo $data;
    }
	
    public function output() {
        $headers = 'Content-type: text/html;';
        
        if ($this->request->xmlOutput) {
            $this->fullXMLOutput();
            die(0);
        }
        
		$headers = '';
		$result = '';
        $expectResult = false;
		foreach ($this->modulesStorage->items as $moduleName => &$module) {
            switch ($module->outputMode) {
                case MODULE_OUTPUT_NONE: break;
				case MODULE_OUTPUT_EXCLUSIVE: 
                    $expectResult = true;
					$result = $module->result;
                    //var_dump($module->header);
                    if (!empty($module->header)) {
                        //var_dump($module->header);
                        //header($module->header);
                        $headers = $module->header;
                    }
                    //if (!empty($result)) $this->raw_output($module->result, $module->header);
				break 2;
				case MODULE_OUTPUT_NORMAL: 
                    $expectResult = true;
					//$headers .= !empty($module['Header']) ? $module['Header'] : 'Content-type: text/html; ';
					$result .= $module->result;
				break;
            }
        }
        
        if ($expectResult && empty($result)) $this->onEmptyResult();
        else {
                //var_dump(headers_sent()); - wow! it returns false when OB enabled! fuck...
            //if (!headers_sent()) {
                //$headers .= 'Access-Control-Allow-Origin: *;';
                //var_dump($headers);
                //$headers .= 'charset=UTF-8;';
                header($headers);
            //}
            echo $result;
        }
	}
	
    public function onEmptyResult() {
        $errors = $this->getErrors();
        $debugMode = true;
        if (!empty($errors) && $debugMode) {
            $this->debugModeErrorsOutput($errors);
        }
    }
    
    protected function debugModeErrorsOutput($errors) {
        echo '<html><body style="padding: 32px; font-family: arial; font-size: 14px; line-height: 24px;"><h3>'.$this->coreInfoString.'</h3>';
        echo 'Something goes wrong:<br/>';
        foreach ($errors as $key => $error) {
            $s = $key.': <span style="color:#CC0000;">'.$error['message'].'</span>';
            if (!empty($error['code'])) $s .= ' [ERR_CODE: '.$error['code'].']';
            if (!empty($error['description'])) $s .= ' Description: '.$error['description'];
            $s .= ' Time: '.date('H:i:m', $error['time']).'<br/>';
            echo $s;
        }
        echo '</body></html>';
    }
    
    public function fullXMLOutput() {
        $view = new View('xml');
        $data['context'] = $this->getContext();
        //$data['loadedModules'] = $this->loadedModules;
        $data['loadedModules'] = Array();
        foreach ($this->modulesStorage->items as $name => $coreModule) {
            $data['loadedModules'][$name] = $coreModule->getAsArray();
        }
        $this->raw_output($view->render($data, ''), $view->getHeader());
    }
    
    public function getLoadedModule($moduleName) {
        if ($this->isModuleLoaded($moduleName)) return $this->modulesStorage->get($moduleName);
        else return false;
    }
    
    public function getLoadedModuleAsArray($moduleName) {
        $module = $this->getLoadedModule($moduleName);
        if (!empty($module)) return $module->getAsArray();
        else return false;
    }
    
    public function isModuleLoaded($moduleName) {
        return $this->modulesStorage->key_exists($moduleName);
    }
    
    public function getLoadedModuleObject($moduleName) {
        $module = $this->getLoadedModule($moduleName);
        if (!empty($module->moduleObject)) return $module->moduleObject;
        else return false;
    }
    
    public function getLoadedModuleData($moduleName) {
        $module = $this->getLoadedModule($moduleName);
        if ($module && !empty($module->data)) return $module->data;
        else return null;
    }
    
    public function addToContext($key, $data) {
        $this->contextExpander[$key] = $data;
    }
    
    public function getContext() {
        # Errors
		$errors = $this->getErrors();
        if (count($errors)) $data['errors']['error'] = $errors;
        
        # Notices
		$notices = $this->getNotices();
        if (count($notices)) $data['notices']['notice'] = $notices;
        
        # Request
        $data['request']['queriedStruct'] = $this->request->queriedStruct;
        $data['request']['parameters'] = $this->request->parameters;
        $data['request']['mobile'] = $this->request->isMobile();
        $data['request']['tablet'] = $this->request->isTablet();

        # Posted Data
        if (count($_POST)) $data['postedData'] = $_POST;
        
        # User
        $auth = $this->getLoadedModuleObject('auth');
        if ($auth) $data['user'] = $auth->getUser();
        //$auth = $this->getLoadedModule('auth');
        //if ($auth) $data['user'] = $auth['data'];
        
        # Loaded modules list
        foreach ($this->modulesStorage->items as $key => $value) {
            $module = array('name' => $key);
            $data['loadedModules']['module'][] = $module;
        }
        
        # Help infos
        $https = $this->request->isHTTPS();
		$data['HTTPS'] = $https ? '1' : '0';
        $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_ADDR'];
		$data['baseURL'] = ($https ? 'https://' : 'http://').$host.'/'.$this->request->siteRoot;
		$data['siteRoot'] = $this->request->siteRoot;
		$data['modulesPath'] = $this->modulesPath;
		$data['siteRootPath'] = $this->siteRootPath;
		$data['siteRootRelativePath'] = $this->siteRootRelativePath;
		$data['configRelativePath'] = $this->request->getConfigRelativePath();
        $configRelativeDir = dirname($data['configRelativePath']);
        if (!empty($configRelativeDir)) $configRelativeDir .= DS;
		$data['configRelativeDir'] = $configRelativeDir;
		$data['projectRelativeDir'] = $data['configRelativeDir'];
		//$data['Request'] = $this->request;
		//$data['LoadedModules'] = &$this->loadedModules; //2Decide - отдавать ли по ссылке? Безопасно?
        
        return array_merge($data, $this->contextExpander);
    }
    
    public function addError($errorMessage, $errorCode = '', $errorDescription = '', $time = null){
        $this->errors->addError($errorMessage, $errorCode, $errorDescription, $time);
    }    
    
    public function addErrorByCode($errorCode, $time = null){
        $errorMessage = $this->errorDecoder->getMessage($errorCode);
        $errorDescription = $this->errorDecoder->getDescription($errorCode);
        $this->errors->addError($errorMessage, $errorCode, $errorDescription, $time);
    }    
    
    public function getErrors(){
        return $this->errors->errors;
    }
    
    public function getLastError(){
        return end($this->errors->errors);
    }
	
    public function errorsCount(){
        return count($this->errors->errors);
    }
    
    public function addNotice($message, $header, $group, $time = null){
        $this->notices->addNodice($message, $header, $group, $time);
    }    
    
    public function getNotices(){
        return $this->notices->notices;
    }

    public function redirectRelativeRoot($path = '', $die = true)
    {
        $configRelativePath = $this->request->getConfigRelativePath();
        if (!empty($configRelativePath)) $configRelativePath = '{'.$configRelativePath.'}/';
        $url = 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->request->siteRoot.$configRelativePath.$path;
        $this->redirect($url, $die);
    }
    
    public function redirect($url, $die = true)
    {
        header('Location: '.$url);
        if ($die) die(0);
    }
    
    public function refresh($die = true)
    {
        $url = $this->request->queriedString;
        if(substr($url, 0, 1) == '/') $url = substr($url, 1);
        $this->redirectRelativeRoot($url, $die);
    }
    
    public function dbErrorCallback($message, $query)
    {
        $lenLimit = 1024;//112;
        if (strlen($query) > $lenLimit) $query = substr($query, 0, $lenLimit).'...';
        $this->addError(utf8_encode($message.' (on running "'.$query.'" query)'));
    }
    
    public function configErrorCallback($message, $code = '') {
        $this->addError(utf8_encode($message.' (on operation with config)'));
    }
    
    public function cacheErrorCallback($message) {
        $this->addError(utf8_encode($message.' (on running cache operation)'));
    }
    
    public function sendClientNoCacheHeaders() {
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Expires: " . date("r"));    
    }
    
    # возвращает путь к папке с модулями относительно корня веб-сервера
    public function getModulesPathRelativeWebRoot() {
        return '/'.$this->request->siteRoot.$this->modulesPath;
	}
}
