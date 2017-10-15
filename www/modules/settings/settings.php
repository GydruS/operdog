<?php

class settings extends SecuredModule
{
	public $templateEngine = 'xslt';
    public $outputMode = MODULE_OUTPUT_NORMAL;
    public $settingPrefix = 'settings:';
    protected $dictionary = null;
    public $defaultSettings = Array(
        'siteName' => Array(
            'type' => 'string',
            'value' => 'GeThree based Site',
        ),
        'style' => Array(
            'type' => 'enum',
            'value' => '',
            'availibleValues' => Array(
                'name' => 'Default',
                'value' => '',
            ),
        ),
        'footer' => Array(
            'type' => 'text',
            'value' => '',
        ),
        'counters' => Array(
            'type' => 'text',
            'value' => '',
        ),
    );

    public function __construct() {
        parent::__construct();
        $this->dictionary = new DBDictionaryModule('storage', 'key');
    }
    
    public function getGuestData($params = null) {
        return array();
    }
    
    public function getUserData($params = null) {
        return array();
    }
    
    public function getAdminData($params = null) {
        if (is_array($params) && array_key_exists('action', $params)) $action = $params['action'];
        else $action = $this->core->request->getParam(1);
        if (empty($action)) $action = $this->core->request->readVar('action', '', SC_REQUEST, TP_STRING);

        $data = parent::getAdminData();
        
        switch ($action) {
            case 'saveSettings': 
				$settings = $this->core->request->readVar('settings', Array(), SC_REQUEST, TP_ARRAY);
				$this->saveSettings($settings);
			break;
        }
        
        $settings = $this->getSettings($this->defaultSettings);
        $settings['style']['availibleValues'] = $this->getAvailibleStyles();

        $data['settings'] = $settings;
        return $data;
    }
    
    public function getAvailibleStyles() {
        $cssFiles = glob(__DIR__.'/../xslt_page_builder/xslt_page_builder_*.css', GLOB_NOSORT);
        $styles['default'] = Array('name' => 'Default', 'value' => '');
        foreach ($cssFiles as $key => $cssFile) {
            $filePart = geCSV_GetLastVal($cssFile,'xslt_page_builder_');
            geCSV_CutLastVal($filePart,'.');
            $styles[$filePart] = Array('name' => $filePart, 'value' => $filePart);
        }
        return $styles;
    }
    
    public function saveSettings($settings) {
		foreach ($settings as $key => $value) {
			$this->saveSetting($key, $value);
		}
	}
	
    public function getSettings($defaultSettings = Array()) {
        $rows = $this->dictionary->getByCriteria('key', $this->settingPrefix.'%', 'LIKE');
		$rows = array_reverse($rows);
		$settings = Array();
        foreach($rows as $subarr) {
			$key = $subarr['key'];
			$pos = strpos($key, $this->settingPrefix);
			if ($pos !== false) $key = substr($key, $pos+strlen($this->settingPrefix));
            $settings[$key]['value'] = $subarr['value'];
        }
		
		# merging
		$combinedSettings = $defaultSettings;
		foreach ($settings as $key => $value) {
			$combinedSettings[$key]['value'] = $value;
		}
		
		return $combinedSettings;
	}
	
    public function getSetting($name, $defaultValue = null) {
		$key = $this->settingPrefix.$name;
        $result = $this->dictionary->getOneBykey($key);
		if ($result === null) {
            if (($defaultValue == null) && isset($this->defaultSettings[$name])) $defaultValue = $this->defaultSettings[$name]['value'];
            $result = $defaultValue;
        }
		else $result = $result['value'];
        return $result;
    }
    
    public function saveSetting($name, $value) {
		$key = $this->settingPrefix.$name;
		$exists = $this->db->quickQuery("SELECT Count(`key`) AS `cnt` FROM $@storage WHERE `key` = {1}", $key);
		$exists = $exists[0]['cnt'];
		if (!$exists) $this->dictionary->addItem(array('key' => $key, 'value' => $value));
		else $this->dictionary->updateItem(array('key' => $key, 'value' => $value));
    }

}
