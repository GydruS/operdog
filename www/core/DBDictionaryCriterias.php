<?php
class DBDictionaryCriterias
{
    public $fieldNames = array();
    public $fieldVals = array();
    public $operations = array();
    public $combinator = 'AND';
    protected $db;
    
    public function __construct($fieldNames = array(), $fieldVals = array(), $operations = array(), $combinator = 'AND') {
        global $db;
        $this->db = $db;
        $this->setCriterias($fieldNames, $fieldVals, $operations, $combinator);
    }
    
    public function setCriterias($fieldNames = array(), $fieldVals = array(), $operations = array(), $combinator = 'AND') {
        $this->fieldNames = is_array($fieldNames) ? $fieldNames : array($fieldNames);
        $this->fieldVals = is_array($fieldVals) ? $fieldVals : array($fieldVals);
        $this->operations = is_array($operations) ? $operations : array($operations);
        $this->combinator = $combinator;
    }
    
    public function addCriterias($fieldNames, $fieldVals, $operations) { //, $combinator = 'AND') {
        $this->fieldNames = array_merge($this->fieldNames, is_array($fieldNames) ? $fieldNames : array($fieldNames));
        $this->fieldVals = array_merge($this->fieldVals, is_array($fieldVals) ? $fieldVals : array($fieldVals));
        $this->operations = array_merge($this->operations, is_array($operations) ? $operations : array($operations));
        //$this->combinator = $combinator;
    }
    
    public function getCriterias($fieldNames = null, $fieldVals = null, $operations = null) {
        if ($fieldNames === null) $fieldNames = $this->fieldNames;
        if ($fieldVals === null) $fieldVals = $this->fieldVals;
        if ($operations === null) $operations = $this->operations;
        
        $criterias = array();
        foreach ($fieldNames as $key => $fieldName) {
            $fieldName = $this->db->escapeFieldName($fieldName);
            $operation = !empty($operations[$key]) ? DBDictionaryCriterias::clearCriteriaOperation($operations[$key]) : '=';
            if ($operation == 'IN') {
				if (is_array($fieldVals[$key])) {
					foreach ($fieldVals[$key] as &$val) $val = $this->db->valueToSQLStr($val);
					$fieldVal = '('.implode(',', $fieldVals[$key]).')';
				}
				else $fieldVal = '('.$this->db->escapeString($fieldVals[$key]).')';
			}
            else $fieldVal = $this->db->valueToSQLStr(isset($fieldVals[$key]) ? $fieldVals[$key] : NULL);
            $criterias[] = $fieldName.' '.$operation.' '.$fieldVal;
        }
        return $criterias;
    }
    
    public function buildWhereConditionFromCriterias($fieldNames = null, $fieldVals = null, $operations = null, $combinator = null) {
        if ($fieldNames === null) $fieldNames = $this->fieldNames;
        if ($fieldVals === null) $fieldVals = $this->fieldVals;
        if ($operations === null) $operations = $this->operations;
        if ($combinator === null) $combinator = $this->combinator;
        if ($combinator !== 'AND') $combinator = 'OR';
        
        $criterias = $this->getCriterias($fieldNames, $fieldVals, $operations);
        $condition = '('.implode(') '.$combinator.' (', $criterias).')';
        return $condition;
    }
    
    public static function clearCriteriaOperation($operation) {
		$operation = strtoupper($operation);
        switch ($operation) {
            case '=':
            case '!=':
            case '>':
            case '<':
            case '<>':
            case '>=':
            case '<=':
            case '=>':
            case '=<':
            case 'LIKE':
            case 'IS':
            case 'NOT IS':
            case 'IN':
            break;
            default:
                $operation = '=';
            break;
        }
        return $operation;
    }
    
}
