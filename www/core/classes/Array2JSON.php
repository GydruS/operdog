<?php

class Array2JSON {
    
    public $doubleTypeSprintfTemplate = '%.16f';
	
	public function __construct($doubleTypeSprintfTemplate = '%.16f') {
		$this->doubleTypeSprintfTemplate = $doubleTypeSprintfTemplate;
	}
	
    public function convert($data) {
        return $this->getJSON($data);
    }
	
    public function isAssocArray($arr) {
        if (empty($arr)) return false;
	    return array_keys($arr) !== range(0, count($arr) - 1);
	}
	
    private function getJSON($data) {
		$dataType = gettype($data);
		if (($dataType == 'array') && $this->isAssocArray($data)) $dataType = 'assocArray';
		
		switch ($dataType) {
			case 'array':
				$c = count($data);
				$i = 0;
				$res = '[';
				foreach ($data as $value) {
					$res .= $this->getJSON($value);
					$i++;
					if ($i<$c) $res .= ',';
				}
				$res .= ']';
			break;
			case 'assocArray':
			case 'object':
				$c = ($dataType == 'assocArray') ? count($data) : count(get_object_vars($data));
				$i = 0;
				$res = '{';
				foreach ($data as $key => $value) {
					$res .= '"'.(string)$key.'":'.$this->getJSON($value);
					$i++;
					if ($i<$c) $res .= ',';
				}
				$res .= '}';
			break;
			case 'double':
				$res = rtrim(sprintf($this->doubleTypeSprintfTemplate, $data), '0');
				$res = rtrim($res, '.');
			break;
			case 'integer':
				$res = (string)$data;
			break;
			case 'boolean':
				$res = $data ? 'true' : 'false';
			break;
			case 'NULL':
				$res = 'null';
			break;
			default:
				$res = '"'.(string)$data.'"';
			break;
		}
		
		return $res;
    }
}
