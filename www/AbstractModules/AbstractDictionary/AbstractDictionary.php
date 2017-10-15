<?php
class AbstractDictionary extends SecuredModule
{
	public $templateEngine = 'xslt';
	public $commonAbstractDictionaryTemplateFile = '';
    public $outputMode = MODULE_OUTPUT_NONE;
    public $doAfterGetItem = false;
	public $autoprocessJsonRequests = true;
	//public $apiWhiteList = Array('');
	public $apiWhiteList = Array('getList', 'getFields', 'getMenu');
	public $apiAdminWhiteList = Array('save', 'delete', 'adminTest');
	//public $provideFieldset = false;
    protected $tableName = '';
    protected $idFieldName = 'id';
    public $dictionary;
    protected $itemName = 'item';
    protected $itemsName = 'items';
    protected $orderFields = '';
    protected $order = 'ASC';
    protected $hiddenFields = Array();

    public function __construct() {
        parent::__construct();
        $this->commonAbstractDictionaryTemplateFile = __DIR__.DIRECTORY_SEPARATOR.'AbstractDictionary.xsl';
        $this->dictionary = new DBDictionaryModule($this->tableName, $this->idFieldName);
        $this->dictionary->parent = $this;
        $this->doAfterGetItem = !empty($this->hiddenFields);
        $this->dictionary->doAfterGetItem = $this->doAfterGetItem;
        $this->orderFields = Array($this->idFieldName);
        $this->updateApiWhiteList();
    }
    
    protected function updateApiWhiteList() {
        $auth = $this->core->getLoadedModuleObject('auth');
        $userType = $auth->getUserType();
        if ($userType == USER_TYPE_ADMIN) {
            $this->apiWhiteList = array_merge($this->apiWhiteList, $this->apiAdminWhiteList);
        }
    }
    
    public function adminTest() {
        return 'Admin test passed!';
    }
    
    public function isAvailibleForAPICall($method) {
        $result = parent::isAvailibleForAPICall($method);
        return $result && in_array($method, $this->apiWhiteList);
    }
    
    public function afterGetItem(&$item) {
        $this->removeHiddenFields($item);
    }

    public function getGuestData($params = null) {
        $data = array();
        return $data;
    }
    
    public function getUserData($params = null) {
        $data['dictionaryInfo'] = $this->getDictionaryInfo();
        $data[$this->itemsName][$this->itemName] = $this->getList();//ByCriteria('active', 1);
        if (!count($data[$this->itemsName][$this->itemName])) unset($data[$this->itemsName][$this->itemName]);
        return $data;
    }
    
    public function getAdminData($params = null) {
        $data = array();
        if ($this->isManageModuleLoaded()) {
            $data['dictionaryInfo'] = $this->getDictionaryInfo();

            if (is_array($params) && array_key_exists('action', $params)) $action = $params['action'];
            else $action = $this->core->request->getParam(1);

            if (empty($action)) {
                $actCreate = $this->core->request->varExists('create', SC_REQUEST);
                if ($actCreate) $action = 'create';

                $editId = $this->core->request->readVar('edit_'.$this->itemName, 0, SC_REQUEST, TP_INT);
                if ($editId) $action = 'edit';

                $deleteId = $this->core->request->readVar('delete_'.$this->itemName, 0, SC_REQUEST, TP_INT);
                if ($deleteId) $this->deleteItem($deleteId);

                $item = $this->core->request->readVar($this->itemName, Array(), SC_REQUEST, TP_ARRAY);
                if (count($item)) $this->saveItem($item);
            }

            switch ($action) {
                case 'create':
                break;
                case 'edit':
                    $data[$this->itemName] = $this->getItem($editId);
                break;
                default: 
                    $action = 'list';
                    $data[$this->itemsName][$this->itemName] = $this->getList();
                    if (!count($data[$this->itemsName][$this->itemName])) unset($data[$this->itemsName][$this->itemName]);
                    //...
                break;
            }
            $data['action'] = $action;
        }
        return $data;
    }
    
    public function getFields() {
        $result = Array();
        $sql = "DESCRIBE $@{$this->tableName}";
        $res = $this->db->quickQuery($sql);
        foreach ($res as $key => $value) {
            if (!in_array($value['Field'], $this->hiddenFields))
                $result[$value['Field']] = Array('name' => $value['Field'], 'type' => $value['Type']);
        }
        return $result;
    }
    
    public function getDictionaryInfo($provideFieldset = false) {
        $info = array();
        $info['itemName'] = $this->itemName;
        $info['itemsName'] = $this->itemsName;
        $info['moduleName'] = get_class($this);
        if ($provideFieldset) $info['fields'] = $this->getFields();
        return $info;
    }
    
    protected function removeHiddenFields(&$item) {
        if (!empty($this->hiddenFields)) {
            foreach ($item as $key => $value) {
                if (in_array($key, $this->hiddenFields)) unset($item[$key]);
            }
        }
    }
    
    public function getItem($itemId) {
        return $this->dictionary->getItem($itemId);
    }
    
    public function getByCriteria($fieldName, $fieldVal, $operation = '=') {
        return $this->dictionary->getByCriteria($fieldName, $fieldVal, $operation);
    }
    
    public function getOneByCriteria($fieldName, $fieldVal, $operation = '=') {
        return $this->dictionary->getOneByCriteria($fieldName, $fieldVal, $operation);
    }
    
    public function getByCriterias(array $fieldNames, array $fieldVals, array $operations, $combinator = 'AND', $orderFields = '', $order = '', $start = 0, $limit = 0) {
		if (empty($orderFields)) $orderFields = $this->orderFields;
		if (empty($order)) $order = $this->order;
        return $this->dictionary->getByCriterias($fieldNames, $fieldVals, $operations, $combinator, $orderFields, $order, $start, $limit);
    }
    
    public function getList($start = 0, $limit = 0) {
        $res = Array();
        if (!empty($this->orderFields)) $res = $this->dictionary->getOrderedList($this->orderFields, $this->order, $start, $limit);
        else $res = $this->dictionary->getList($start, $limit);
        return $res;
    }
    
    public function getItemInfo($itemId) {
        $data = $this->dictionary->getByCriteria($this->idFieldName, $itemId);
        return $data;
    }
    
    public function isManageModuleLoaded() {
        return $this->core->isModuleLoaded('manage');
    }
    
    public function save() {
        $success = false;
        $item = $this->core->request->readVar($this->itemName, Array(), SC_REQUEST, TP_ARRAY);
        if (!empty($item)) {
            $success = $this->saveItem($item);
        }
        return Array('success' => $success);
    }
    
    public function delete() {
        $success = false;
        $itemId = $this->core->request->readVar($this->idFieldName, 0, SC_REQUEST, TP_INT);
        if (!empty($itemId)) {
            $success = $this->deleteItem($itemId);
        }
        return Array('success' => $success);
    }
    
    protected function saveItem($item) {
        $justAdded = false;
        if (!empty($item[$this->idFieldName])) $item[$this->idFieldName] = (int)$item[$this->idFieldName];
        if ($this->processItemBeforeSave($item)) {
            if (!empty($item[$this->idFieldName])) $this->dictionary->updateItem($item);
            else {
                $item[$this->idFieldName] = $this->dictionary->addItem($item);
                $justAdded = true;
            }
            $this->afterItemSaved($item, $justAdded);
            return $item[$this->idFieldName];
        }
        else return false;
    }
    
    protected function processItemBeforeSave(&$item) {
        return true;
    }
    
    protected function afterItemSaved(&$item, $justAdded = false) {
        // ...
    }
    
    protected function deleteItem($itemId) {
        $result = false;
        if ($this->beforeItemDelete($itemId)) $result = $this->db->query('DELETE FROM `$@'.$this->tableName.'` WHERE `'.$this->idFieldName.'` = {1}', $itemId);
        return $result;
    }
    
    protected function beforeItemDelete($itemId) {
        return true;
    }
    
    public function getIdsString($items) {
        $ids = Array();
        foreach ($items as $item) {
            $ids[] = $item[$this->idFieldName];
        }
        return implode(',',$ids);
    }
    
    public function getScripts() {
        $scripts = parent::getScripts();
		return $scripts;
	}
	
	// TODO: Наследовать от DBDictionaryModule (а его, соотв., переделать на наследование от SecuredModule).
	// И таким образом убрать код ниже!
	public function getListPage($page = 1, $pageSize = 0) {
		return $this->dictionary->getListPage($page, $pageSize);
    }
    
    public function getPaginationData(DBDictionaryCriterias $selectCriterias = null) {
		return array(
            'perPage' => $perPage = $this->core->request->readVar('itemsPerPage', 10, SC_COOKIE, TP_INT),
            'page' => $this->core->request->readVar('page', 1, SC_GET, TP_INT),
            'totalPages' => ($perPage <= 0) ? '1' : $this->dictionary->getPagesCount($perPage, $selectCriterias),
        );
    }
    
    public function getItemsCount(DBDictionaryCriterias $selectCriterias = null) {
		return $this->dictionary->getItemsCount($selectCriterias);
    }

}
