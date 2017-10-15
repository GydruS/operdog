<?php
#################################
#       GydruS's Engine 3       #
#          "SQL" class          #
#             v. 1.0            #
#    2012 10 09 - 2012 10 10    #
#################################

#################################
# Description
#--------------------------------
#
#

class DB
{
    private $dbAdapter;
	private $queriesLogger;
	public $logQueries = FALSE;
	public $dbTablesPrefix = '';
	public $errorCallback = null;
 
    public function __construct($engine, $errorCallback = null) {
        $this->errorCallback = $errorCallback;
		switch ($engine) {
			case 'mysqli' : Load::dbAdapter('MySQLiAdapter'); $this->dbAdapter = new MySQLiAdapter(); break;
			case 'mysql' : Load::dbAdapter('MySQLAdapter'); $this->dbAdapter = new MySQL(); break;
			case 'mssql' : Load::dbAdapter('MSSQLAdapter'); $this->dbAdapter = new MSSQL(); break;
 		}
	}
	
    public function connect($host, $user, $password) {
		$res = $this->dbAdapter->connect($host, $user, $password);
        if ($res) $this->query('SET NAMES `utf8`');
        return $res;
    }
	
    // возвращает чистый SQL после применения всех фильтров, очисток и т.п. и т.д.,
    // т.е. то самое SQL-выражение, которое уже отдается СУБД.
    public function getPreparedSQL($sql) {
        //tables names should be started with '$@', so change it with prefixes
		$sql=str_replace("$@", $this->dbTablesPrefix, $sql);
		if ( func_num_args() > 1 ) {
			$params = func_get_args();
			array_shift($params);
			$sql = $this->placeholderProcess($sql, $params);
		}
        return $sql;
    }
    
    protected function logQuery($sql) {
        if (empty($this->queriesLogger)) $this->queriesLogger = new DataKeeper();
        $this->queriesLogger->add($sql);
    }
    
    public function getLoggedQueries() {
        $res = !empty($this->queriesLogger) ? $this->queriesLogger->getAll() : array();
        return $res;
    }
    
    public function query($sql) {
        $params = func_get_args();
        $sql = call_user_func_array(array($this,'getPreparedSQL'), $params);
        return $this->directQuery($sql);
/*        
		$resource = $this->dbAdapter->query($sql);
		if ( !$resource ) {
			/ *$geDB_LogQueries == 1 ? $q_Number = ' #'.geSM_QueriesCount() : $q_Number = '';
			geSM_AppendErrorMessage('Failed while executing query'.$q_Number.': '.$argSql.' MySQL said: '.geDB_GetLastError().' ErrorNo.:'.geDB_GetLastErrorCode());
			return GE_RESULT_FAILED;* /
            $this->callErrorCallback($sql);
			return FALSE;
		}
		/ *if ( $resource === true )
		{
			return true;//GE_RESULT_OK;
		}else{
			return $resource;
		}* /
		return $resource;
*/
	}

    public function directQuery($sql) {
		if ($this->logQueries) $this->logQuery($sql);
		$res = $this->dbAdapter->query($sql);
        if (!$res) $this->callErrorCallback($sql);
        return $res;
    }
    
    public function multiQuery($sql) {
        $params = func_get_args();
        $sql = call_user_func_array(array($this,'getPreparedSQL'), $params);
        return $this->directMultiQuery($sql);
	}
    
    public function directMultiQuery($sql) {
		if ($this->logQueries) {/*...*/}
		$res = $this->dbAdapter->multiQuery($sql);
        if (!$res) $this->callErrorCallback($sql);
        return $res;
	}
    
    protected function callErrorCallback($sql)
    {
        if (!empty($this->errorCallback))
        {
            $message = $this->getLastError();
            call_user_func($this->errorCallback, $message, $sql);
        }
    }
		
    public function quickQuery($sql) {
		//global $geDB_StripSlashes;
		$params = func_get_args();

		/*
		$CacheKey = '';
		foreach($params as $k=>$v) if($k != 0) $CacheKey .= $v.';';
		if ($CacheKey != '') $CacheKey = $sql.' with params: '.$CacheKey;
		else $CacheKey = $sql;
		$Cache = geCacher_GetCache($CacheKey);
		if($Cache)
		{
			if ($this->logQueries) /*geSM_AppendSQLQuery($CacheKey, true)* /;
			return $Cache;
		}
		*/

		//$res = call_user_func_array('geDB_Query', $params);
		$res = call_user_func_array(array($this,'query'), $params);
		$ret = array();
		if ( is_integer($res) )
		{
			return $res;
		}
		else
		{
			/*if ($geDB_StripSlashes)
			{
				$nums=array();
				$i=0;
				while($i<mysql_num_fields($res))
				{
				   $meta=mysql_fetch_field($res,$i);
				   if($meta->type=="string" || $meta->type=="blob") array_push($nums,$i);
				   $i++;
				}
			}*/
			while ( $row = $this->dbAdapter->fetchArray($res) )
			{
				/*if ($geDB_StripSlashes)
				{
					$i=0;
					foreach($row as &$v)
					{
						if(array_search($i,$nums)!==false)
						{
						   $v=stripslashes($v);
						}
						$i++;
					}
					unset($v);
				}*/
				$ret[] = $row;
			}
		}
		//geCacher_SetCache($CacheKey, $ret);
		return $ret;
    }
	
    public function select($sql) {
		$params = func_get_args();
		$res = call_user_func_array(array($this,'quickQuery'), $params);
		if (is_array($res) && count($res)) return $res;
		else return NULL;
	}
	
    public function selectOne($sql) {
		$params = func_get_args();
		$res = call_user_func_array(array($this,'quickQuery'), $params);
		if (is_array($res) && count($res)) return $res[0];
		else return NULL;
	}
    
    public function selectField($field, $sql) {
		$params = array_slice(func_get_args(),1);
		$record = call_user_func_array(array($this,'selectOne'), $params);
		return isset($record[$field]) ? $record[$field] : NULL;
    }
	
	/**
	 * Построение выражения по ассоциативному массиву
	 *
	 * Этот метод строит выражение используемое в
	 * запросах INSERT из ассоциативного массива
	 * например для массива:
	 * <code>
	 * array('id'=>5, 'name'=>'Fred', age=>"2333'");
	 * </code>
	 * он вернет массив:
	 * <code>
	 * array("id = '5'", "name = 'Fred'", "age='2333\''");
	 * </code>
	 * @param array $argPieces входной массив
	 * @return array
	 */
	public function buildPairStrings($argPieces)
	{
		$ret = array();
		if (is_array($argPieces))
			foreach ( $argPieces as $key=>$value )
			{
				$key = $this->dbAdapter->escapeString($key);// addslashes($key);
				//$value = $this->dbAdapter->escapeString($value);// addslashes($value);
				//$ret[] = "`{$key}` = '{$value}'";
                $pair = "`{$key}` = ".$this->valueToSQLStr($value);//(($value === NULL) ? 'NULL' : "'{$this->dbAdapter->escapeString($value)}'");
				$ret[] = $pair;
			}
		return $ret;
	}

	public function buildUpdateClause($argPieces)
	{
		return implode(',', $this->buildPairStrings($argPieces));
	}
    
    // очищаем массив от пустых данных (допустимо удалять только строки)
	protected function clearValues($array) {
        foreach ($array as $key => $value) {
            if (is_string($value) && ($value === '')) unset($array[$key]);
        }
        return $array;
    }
    
    // экранируем и подготавливаем значения для прямой вставки в запрос
	public function valuesToSQLStr($array) {
        foreach ($array as $key => $value) $array[$key] = $this->valueToSQLStr($value);
        return $array;
    }
    
    // экранируем имя поля для прямой вставки в запрос
	public function escapeFieldName($field) {
        if (!is_string($field)) return false;
        $fieldParts = explode('.', trim($field));
        foreach ($fieldParts as $fpKey => $fieldPart) {
            $fieldPart = trim($fieldPart);
            if ($fieldPart != '*') $fieldPart = '`'.str_replace('`', '', $fieldPart).'`';
            $fieldParts[$fpKey] = $fieldPart;
        }
        $field = implode('.', $fieldParts);
        return $field;
    }
    
    // экранируем имена полей для прямой вставки в запрос
	public function escapeFieldNames($fields) {
        if (is_string($fields)) $fields = explode(',', $fields);
        elseif (!is_array($fields)) return false;
        
        foreach ($fields as $key => $field) {
            $field = $this->escapeFieldName($field);
            if ($field !== false) $fields[$key] = $field;
            else unset($fields[$key]);
        }
        
        $fields = implode(',', $fields);
        return $fields;
    }
    
    // если для вставки передается массив итемов, а не 1 итем, то все итемы массива должны иметь одинаковые списки полей
	public function buildInsertClause($itemOrItems, $clearValuesWhen1Insert = true) {
		if (is_array($itemOrItems) && count($itemOrItems)) {
            $batchInsert = isset($itemOrItems[0]) && is_array($itemOrItems[0]); // строим выражение для вставки одной записи или нескольких

            if (!$batchInsert && $clearValuesWhen1Insert) $itemOrItems = $this->clearValues($itemOrItems);

            $fieldNames = $batchInsert ? array_keys($itemOrItems[0]) : array_keys($itemOrItems);
            $fieldNamesStr = '('.$this->escapeFieldNames($fieldNames).')';

            $valuesStr = '';
            if ($batchInsert) {
                $valuesStrs = Array();
                foreach ($itemOrItems as $item) {
                    $valuesStrs[] = '('.implode(',', $this->valuesToSQLStr($item)).')';
                }
                $valuesStr = implode(',', $valuesStrs);
                //ToDo:!!! try just 1 - $valuesStr = '('.implode('),(', $valuesStrs).')';
            }
            else $valuesStr = '('.implode(',', $this->valuesToSQLStr($itemOrItems)).')';

            $insertClause = $fieldNamesStr.' VALUES '.$valuesStr;

            return $insertClause;
        }
        return false;
	}
    
    /*
	public function buildInsertClause0($ar)
	{
		if (is_array($ar))
		{
        // очищаем массив от пустых данных (допустимо удалять только строки)
        foreach ($ar as $key => $value) {
            if (is_string($ar[$key]) && $ar[$key] === '') unset($ar[$key]);
        }

        $c = count($ar);
        $i=0;

        $flds = '(';
        $vals = ' VALUES (';
        foreach ($ar as $key => $value)
        {
            $i++;
            // формируем список полей
            # if($value != end($ar)) <-- мало того, что так глючит PHP, так тут еще будет облом, когда некий элемент массива по
            # значению будет равен последнему эл-ту!!!
            if($i < $c) $flds = $flds.'`'.$key.'`, ';
            else $flds = $flds.'`'.$key.'`)';
            // формируем список значений
            # if($value != end($ar)) <-- мало того, что так глючит PHP, так тут еще будет облом, когда некий элемент массива по
            # значению будет равен последнему эл-ту!!!
            if($i < $c) $vals = $vals.$this->valueToSQLStr($value).", ";
            else $vals = $vals.$this->valueToSQLStr($value).")";
//				if($i < $c) $vals = $vals."'".$this->dbAdapter->escapeString($value)."', ";
//				else $vals = $vals."'".$this->dbAdapter->escapeString($value)."')";
        }
        return ($flds.' '.$vals);
	}
		else 
		{
			//appendErrorMessage('Invalid Arument at GetInsertClause function!');
		}
	}
    */
    
	public function valueToSQLStr($value)
	{
        return (($value === NULL) ? 'NULL' : "'{$this->escapeString($value)}'");
    }

	/**
	 * Обработка placeholder'ов
	 *
	 * Эта функция обрабатывает строку содержащую placeholder'ы превращая ее
	 * в полноценный запрос.
	 * @param string $stringWithPlaceholders Строка с placeholder'ами
	 * @param array $argParams Массив параметров
	 * @return string
	 */
	public function placeholderProcess($stringWithPlaceholders, $argParams) {
		if ( !is_string($stringWithPlaceholders) || !is_array($argParams) ) {
			//appendErrorMessage('Invalid params at placeholderProcess function!');
			//return GE_INVALID_PARAM;
			return '';
		}
		$argParams = array_values($argParams);
		$argParamsCnt = count($argParams);
		for ( $i=0; $i<$argParamsCnt; $i++ ) {
            $doEscape = true;
            $wrapByQuotas = true;
			switch ( gettype($argParams[$i]) ) {
				case 'NULL':
                    $argParams[$i] = 'NULL';
                    $doEscape = false;
                    $wrapByQuotas = false;
				break;

				case 'string':
				break;

				case 'integer':
				case 'float':
				case 'double':
					$argParams[$i] = (string)$argParams[$i];
                    $wrapByQuotas = false;
				break;

				case 'boolean':
					$argParams[$i] = $argParams[$i] ? '1' : '0';
                    $wrapByQuotas = false;
				break;

				default:
					if ($argParams[$i] != '') {
						//geSM_AppendErrorMessage('Invalid parameter "'.$argParams[$i].'" in argument at placeholderProcess function!');
						//return GE_INVALID_VALUE;
						return FALSE;
					}
				break;
			}
            if ($doEscape) $argParams[$i] = $this->dbAdapter->escapeString($argParams[$i]); //addslashes($argParams[$i]);
            if ($wrapByQuotas) $argParams[$i] = '\''.$argParams[$i].'\'';
		}
        
		// Обрабатываем плейсхолдеры
        $result = $stringWithPlaceholders;
        foreach ($argParams as $key => $param) {
            $placeHolder = '{'.($key+1).'}';
            $result = str_replace($placeHolder, $param, $result);
        }
        return $result;

        /*
		// Обрабатываем плейсхолдеры с помощью REGEXP
		$ret = preg_replace("/\{(\d)\}/e", 'isset($argParams[\\1-1]) ? "\'".$argParams[\\1-1]."\'" : (is_null($argParams[\\1-1]) ? "NULL" : \'{\\1}\')', $stringWithPlaceholders);
		//$ret = preg_replace("/\{(\d)\}/e", 'is_null($argParams[\\1-1]) ? (isset($argParams[\\1-1]) ? "NULL" : \'{\\1}\') : "\'".$argParams[\\1-1]."\'"', $stringWithPlaceholders);
		//$ret = preg_replace("/\{(\d)\}/e", 'isset($argParams[\\1-1]) ? "\'".$argParams[\\1-1]."\'" : \'{\\1}\'', $stringWithPlaceholders);
		return $ret;
        */
	}
	
	public function buildWhereCondition($FieldName, $Values)
	{
		$Condition = "WHERE ( $FieldName = ";
		$Condition .= implode(" or $FieldName = ", $Values);
		$Condition .= ")";
		return $Condition;
	}
	
	public function startTransaction()
	{
		$this->query("BEGIN");
	}

	public function commitTransaction()
	{
		$this->query("COMMIT");
	}

	public function rollbackTransaction()
	{
		$this->query("ROLLBACK");
	}

	public function currentDB()
	{
		$res = $this->selectOne("SELECT DATABASE() as `dbname`");
		return $res['dbname'];
	}	
	
	public function selectDB($database)
	{
		return $this->dbAdapter->selectDB($database);
	}	
	
	public function lastInsertId()
	{
		return $this->dbAdapter->lastInsertId();
	}	
	
	public function getLastError()
	{
		return $this->dbAdapter->getLastError();
	}
    
	public function escapeString($str) {
        return $this->dbAdapter->escapeString($str);
    }
    
    public function setIdsToArrayKeys($records, $idFieldName = 'id') {
        return $this->setArrayKeysFromSubArraysFields($records, $idFieldName);
    }
    
    public function setArrayKeysFromSubArraysFields($arr, $newKeyValFieldName) {
        //foreach($arr as $key => $subarr) array_change_key_name( $key, $subarr[$newKeyValFieldName], $arr );
        $result = array();
        foreach($arr as $key => $subarr) {
            //unset ($arr[$key]);
            //$arr[$subarr[$newKeyValFieldName]] = $subarr;
            $result[$subarr[$newKeyValFieldName]] = $subarr;
        }
        return $result;
    }    
    
}
