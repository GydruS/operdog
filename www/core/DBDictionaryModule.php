<?php
class DBDictionaryModule extends Module
{
    public $tableName = '';
    public $idFieldName = 'id';
    public $pageSize = 10;
    public $selectCriterias = null;
    public $doAfterGetItem = false;
    public $parent = null;
    private $fieldsList = '*';
    
    public function __construct($tableName = '', $idFieldName = '', DBDictionaryCriterias $criterias = null) {
        parent::__construct();
        if (!empty($tableName)) $this->tableName = $tableName;
        if (!empty($idFieldName)) $this->idFieldName = $idFieldName;
        if (!empty($criterias)) $this->selectCriterias = $criterias;
    }
    
    public function __call($method, $arguments) {
        if (!method_exists($this, $method))
        {
            //$fieldName = ''; commented to able throws error on wrong call!!!
            if (strpos($method, "getOneBy") !== false) {
                $fieldName = str_ireplace("getOneBy", "", $method);
                $method = 'getOneByCriteria';
            }
            elseif (strpos($method, "getBy") !== false) {
                $fieldName = str_ireplace("getBy", "", $method);
                $method = 'getByCriteria';
            }
            array_unshift($arguments, $fieldName);
            $res = call_user_func_array(array($this, $method), $arguments);
            return $res;
        }
	}    
    
    public function setFieldsList($fields = '*') {
        $escapedFields = $this->db->escapeFieldNames($fields);
        if ($escapedFields !== false) $this->fieldsList = $escapedFields;
        return $escapedFields;
    }
    
    public function getFieldsList() {
        return $this->fieldsList;
    }
    
    public function getItems($start = 0, $limit = 0) {
        return $this->getList($start, $limit);
    }
    
    public function getPagesCount($pageSize = 0, DBDictionaryCriterias $selectCriterias = null) {
        if (empty($pageSize)) $pageSize = $this->pageSize;
        if (empty($pageSize)) return 1;
        $count = $this->getItemsCount($selectCriterias);
        $pagesCount = ceil($count / $pageSize);
        return $pagesCount;
    }
    
    public function getListPage($page = 1, $pageSize = 0, $orderFields = '', $order = '') {
        if (empty($pageSize)) $pageSize = $this->pageSize;
        return $this->getList(($page-1)*$pageSize, $pageSize, $orderFields, $order);
    }
    
    public function getList($start = 0, $limit = 0, $orderFields = '', $order = '') {
        if (empty($fields) && empty($order)) $res = $this->getListWithExtendedCondition($this->getLimitCondition($start, $limit));
        else $res = $this->getOrderedList($orderFields, $order, $start, $limit);
        return $res;
    }
    
    public function getOrderedList($orderFields = '', $order = '', $start = 0, $limit = 0) {
        return $this->getListWithExtendedCondition($this->getOrderByCondition($orderFields, $order).' '.$this->getLimitCondition($start, $limit));
    }
    
    private function getOrderByCondition($orderFields = '', $order = 'ASC') {
        $orderByCond = '';
        if (empty($orderFields)) $orderFields = $this->idFieldName;
        if (strtoupper($order) !== 'ASC') $order = 'DESC';
        $orderFields = $this->db->escapeFieldNames($orderFields);
        $orderByCond = 'ORDER BY '.$orderFields.' '.$order;
        return $orderByCond;
    }
    
    private function getLimitCondition($start = 0, $limit = 0) {
        $limitCond = '';
        if ($start && $limit) $limitCond = 'LIMIT '.(int)$start.', '.(int)$limit;
        if (!$start && $limit) $limitCond = 'LIMIT '.(int)$limit;
        return $limitCond;
    }
    
    private function getListWithExtendedCondition($condition = '') {
        $selectCriteriasCondition = $this->getConditionFromCriterias();
        if (!empty($selectCriteriasCondition)) $selectCriteriasCondition = ' WHERE '.$selectCriteriasCondition;
        //var_dump($this->db->getPreparedSQL('SELECT '.$this->fieldsList.' FROM `$@'.$this->tableName.'` '.$selectCriteriasCondition.' '.$condition));
        $items = $this->db->quickQuery('SELECT '.$this->fieldsList.' FROM `$@'.$this->tableName.'` '.$selectCriteriasCondition.' '.$condition);
        if ($this->doAfterGetItem) $this->afterGetItems($items);
        return $items;
    }
    
    public function getItem($id) {
        return $this->getOneByCriteria($this->idFieldName, $id);
    }
    
    private function getOne($res) {
		if (is_array($res) && count($res)) return $res[0];
		else return NULL;
	}
	
    public function getOneByCriteria($fieldName, $fieldVal, $operation = '=') {
        $res = $this->getByCriteria($fieldName, $fieldVal, $operation);
		return $this->getOne($res);
    }
    
    public function getOneByCriterias(array $fieldNames, array $fieldVals, array $operations, $combinator = 'AND') {
        $res = $this->getByCriterias($fieldNames, $fieldVals, $operations, $combinator);
		return $this->getOne($res);
    }
    
    public function setSelectCriterias(DBDictionaryCriterias $criterias) {
        $this->selectCriterias = $criterias;
    }
    
    public function getByCriteria($fieldName, $fieldVal, $operation = '=', $start = 0, $limit = 0) {
        $operation = DBDictionaryCriterias::clearCriteriaOperation($operation);
        $fieldName = $this->db->escapeFieldName($fieldName);
        $limitCondition = $this->getLimitCondition($start, $limit);
        $items = $this->db->quickQuery('SELECT '.$this->fieldsList.' FROM `$@'.$this->tableName.'` WHERE '.$fieldName.' '.$operation.' {1} '.$limitCondition, $fieldVal);
        if ($this->doAfterGetItem) $this->afterGetItems($items);
        return $items;
        //return $this->getByCriterias(array($fieldName), array($fieldVal), array($operation));
    }
    
    public function getByCriterias(array $fieldNames, array $fieldVals, array $operations, $combinator = 'AND', $orderFields = '', $order = 'ASC', $start = 0, $limit = 0) {
		$orderCondition = '';
		if (!empty($orderFields)) $orderCondition = $this->getOrderByCondition($orderFields, $order);
        $limitCondition = $this->getLimitCondition($start, $limit);
        $selectCriterias = new DBDictionaryCriterias($fieldNames, $fieldVals, $operations, $combinator);
        $condition = $this->getConditionFromCriterias($selectCriterias);
        if (!empty($condition)) $condition = ' WHERE '.$condition;
        $sql = 'SELECT '.$this->fieldsList.' FROM `$@'.$this->tableName.'` '.$condition.' '.$orderCondition.' '.$limitCondition;
        $items = $this->db->quickQuery($sql);
        if ($this->doAfterGetItem) $this->afterGetItems($items);
        return $items;
    }
    
    /*
    private function buildWhereConditionFromCriterias(array $fieldNames, array $fieldVals, array $operations, $combinator = 'AND') {
        if ($combinator !== 'AND') $combinator = 'OR';
        $parts = array();
        foreach ($fieldNames as $key => $fieldName) {
            $fieldName = $this->db->escapeString($fieldName);
            $fieldVal = !empty($fieldVals[$key]) ? $this->db->escapeString($fieldVals[$key]) : NULL;
            $operation = !empty($operations[$key]) ? $this->clearCriteriaOperation($operations[$key]) : '=';
            $parts[] = '(`'.$fieldName.'` '.$operation.' '.$fieldVal.')';
        }
        $condition = implode(' '.$combinator.' ', $parts);
        var_dump($condition);
        return $condition;
    }
    
    private function clearCriteriaOperation($operation) {
        switch ($operation) {
            case '=':
            case '>':
            case '<':
            case '<>':
            case '>=':
            case '<=':
            case '=>':
            case '=<':
            break;
            default:
                $operation = '=';
            break;
        }
        return $operation;
    }
    */
    
    public function addItem($item) {
		$insertClause = $this->db->buildInsertClause($item, false);
		if ($this->db->query('INSERT INTO `$@'.$this->tableName.'` '.$insertClause)) return $this->db->lastInsertId();
        else return false;
    }
    
    public function addItems($items) {
		$insertClause = $this->db->buildInsertClause($items);
		return $this->db->query('INSERT INTO `$@'.$this->tableName.'` '.$insertClause);
    }
    
    public function getPreparedUpdateItemSQL($item) {
        $id = $item[$this->idFieldName];
        unset($item[$this->idFieldName]);
		$updateClause = $this->db->buildUpdateClause($item);
        $idFieldName = $this->db->escapeFieldName($this->idFieldName);
		return $this->db->getPreparedSQL('UPDATE `$@'.$this->tableName.'` SET '.$updateClause.' WHERE '.$idFieldName.'={1};', $id);
    }
    
    public function updateItem($item) {
        $sql = $this->getPreparedUpdateItemSQL($item);
		return $this->db->directQuery($sql);
    }
    
    public function deleteItem($id) {
        $idFieldName = $this->db->escapeFieldName($this->idFieldName);
		return $this->db->query('DELETE FROM `$@'.$this->tableName.'` WHERE '.$idFieldName.'={1};', $id);
    }
    
    public function getRandomItem(DBDictionaryCriterias $selectCriterias = null) {
        $count = $this->getItemsCount($selectCriterias);
        $offset = rand(0, $count-1);
        $condition = $this->getConditionFromCriterias($selectCriterias);
        if (!empty($condition)) $condition = ' WHERE '.$condition;
        $item = $this->db->selectOne('SELECT '.$this->fieldsList.' FROM `$@'.$this->tableName.'` '.$condition.' LIMIT '.(int)$offset.',1');
        if ($this->doAfterGetItem) $this->afterGetItem($item);
		return $item;
    }
    
    public function getItemsCount(DBDictionaryCriterias $selectCriterias = null) {
        $condition = $this->getConditionFromCriterias($selectCriterias);
        if (!empty($condition)) $condition = ' WHERE '.$condition;
        $count = $this->db->quickQuery('SELECT COUNT(*) AS `count` FROM `$@'.$this->tableName.'` '.$condition);
		return $count[0]['count'];
    }
    
    public function getConditionFromCriterias(DBDictionaryCriterias $selectCriterias = null) {
        if ($selectCriterias === null) $selectCriterias = $this->selectCriterias;
        $condition = (!empty($selectCriterias)) ? $selectCriterias->buildWhereConditionFromCriterias() : '';
        return $condition;
    }
    
    public function afterGetItems(&$items) {
        if (is_array($items))
            foreach ($items as $key => $item)
                $this->afterGetItem($items[$key]);
    }
    
    public function afterGetItem(&$item) {
        //if (is_callable($this->externalAfterGetItem)) $this->externalAfterGetItem($item);
        if (!empty($this->parent) && method_exists($this->parent, 'afterGetItem')) $this->parent->afterGetItem($item);
    }
    
    public function generateUUID() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
}
