<?php
#################################
#       GydruS's Engine 3       #
#     "QueriedStruct" class     #
#             v. 1.0            #
#           2012 10 08          #
#################################

#################################
# Description
#--------------------------------
# This library parses REQUEST_URI like '/mr/{../siteY/conf.php}/test/test2/test3/t4/5/6/7/8/9?qwe=1&asd=2'
# into GEngine Three internal structure.

#--------- SCOPES -----------------------
define('SC_GET',                    1);
define('SC_POST',                   2);
define('SC_COOKIE',                 4);
define('SC_SESSION',                8);
define('SC_FILES',                  16);
define('SC_REQUEST',                SC_GET|SC_POST|SC_FILES|SC_COOKIE);
define('SC_BIT_LENGHT',             5);


class Request extends Singleton {
    public $siteRoot = '';
	public $queriedStruct = Array();
	public $queriedString = '';
	public $parameters = Array();
	public $xmlOutput = false;		# пользователь запросил xml
	public $jsonOutput = false;		# пользователь запросил json
    private $configRelativePath = '';
    private $scopes = Array();
//    private $siteRootRelativePath = '';
        
    private function __construct($siteRoot = '') {
		$this->updateGetArray();
        $this->scopes = array(
            SC_COOKIE  => $_COOKIE,
            SC_GET     => $_GET,
            SC_POST    => $_POST,
            SC_FILES    => $_FILES,
            SC_SESSION => isset($_SESSION) ? $_SESSION : array(),
        );
		$this->siteRoot = ($siteRoot === '') ? $this->getRootDir() : $siteRoot;
		$this->makeQueriedStruct();
    }
	
    public static function getInstance($siteRoot = '') {
        // проверяем актуальность экземпляра
        if (self::$_instance === null) {
            // создаем новый экземпляр
            self::$_instance = new self($siteRoot);
        }
        // возвращаем созданный или существующий экземпляр
        return self::$_instance;
    }
    
    public function getParam($paramIndex) {
        return (!empty($this->parameters[$paramIndex])) ? $this->parameters[$paramIndex] : null;
    }
    
    public function getQueriedModuleName($moduleIndex) {
        return (!empty($this->queriedStruct[$moduleIndex])) ? $this->queriedStruct[$moduleIndex] : null;
    }
	
	public function getUriParamsAsArray($urlDecode = true) {
		$str = geCSV_GetLastVal($_SERVER['REQUEST_URI'],'?');
		$vals = geCSV_ParsePairedLine($str, false, '&');
        if ($urlDecode) foreach ($vals as $key => $val) $vals[$key] = urldecode($val);
        return $vals;
	}
    
	protected function updateGetArray() {
		$_GET = array_merge($_GET, $this->getUriParamsAsArray());
	}
	
	private function makeQueriedStruct() {
        $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_ADDR'];
		$prefix = $host.'/'.$this->siteRoot;
		if ($prefix != '') {
			// получаем строку запроса
			$str = geCSV_GetFirstVal($host.$_SERVER['REQUEST_URI'],'?');
			// отрезаем от строки запроса адрес сайта
			$cPos = strpos($str, $prefix);
			if ($cPos !== false) {
				$cl = strlen($prefix);
				$str = substr($str, $cPos+$cl, strlen($str)-$cPos-$cl);
			}
			// тут своими силами определим - запрашивает ли юзер файл XML
			$flg = strpos($str, '.xml');
			if($flg !== false) {
				$this->xmlOutput = true;
				$str = geCSV_CutFirstVal($str,'.xml');
			}
				// или юзер запрашивает JSON
				else {
					$flg = strpos($str, '.json');
					if($flg !== false) {
						$this->jsonOutput = true;
						$str = geCSV_CutFirstVal($str,'.json');
					}
				}

			// забиваем в структуру данные строки запроса
            $this->processQueryString($str);
		}
	}
    
    private function processQueryString($str) {
        // декодируем URL
        $str = urldecode($str);
        // обрабатываем служебные "модификаторы" из строки запроса
        $str = $this->processDirectives($str);
        
        $this->queriedString = $str;
            
        // строим саму структуру запроса
        while (strlen($str)>0)
        {
            $val = geCSV_CutFirstVal($str,'/');
            if (!empty($val)) $this->queriedStruct[] = $val;
        }
    }
    
    // получаем все служебные "модификаторы" из строки запроса и настраиваем класс по ним
    private function processDirectives($str) {
        $pattern = "/\{.*\}/m";
        $n = preg_match_all ($pattern, $str, $res); //осуществляем поиск
        for ($i=0;$i<$n;$i++) { //обрабатываем результаты 
            $val = substr($res[0][$i],1,-1);
            $this->configRelativePath = $val;
        }
        
        // вырезаем все служебные "модификаторы" и возвращаем строку без них
        $str = preg_replace($pattern, '', $str);
        
        return $str;
    }
    
    /*private function isSpecValue($value, $openSpecifier = '{', $closeSpecifier = '}') {
        if (!empty($value) && ($value{0} == $openSpecifier) && ($value{strlen($value)-1} == $closeSpecifier)) {
            return true; // substr($value,1,-1);
        }
        else return false;
    }*/
	
	protected function getRootDir() {
		$items = geCSV_StringToArray($_SERVER['SCRIPT_NAME'], '/', true);
		$c = count($items);
		if ($c > 1) {
			unset($items[$c-1]);
			return implode('/', $items).'/';
		}
		else return '';
	}
    
    public function readVar($argName, $argDefault = '', $argScope = SC_GET, $argType = TP_STRING) {
        if (!is_string($argName) || !is_integer($argScope) || strlen($argName) == 0 || !is_integer($argType)) return NULL;
        
        for($i=0; $i<=SC_BIT_LENGHT; $i++) {
            // Проверяем по порядку все биты в $argScope (начиная со старшего)
            $bit = $argScope & (1<<$i);
            $varValue = NULL;
            // Получаем источник данных
            if (isset($this->scopes[$bit])) {
                $scope = $this->scopes[$bit];
                // Проверяем наличие переменной в источнике
                if (isset($scope[$argName])) $varValue = $scope[$argName]; // с учетом регистра
                else { // без учета регистра
                    foreach ($scope as $key => $value) {
                        if (strtolower($argName) == strtolower($key)) $varValue = $value;
                    }
                }
                
                if ($varValue !== NULL) {
                    // Приводим к типу
                    $varValue = $this->applyType($argType, $varValue, $argDefault);
                    
                    if (func_num_args() > 4) {
                        // Обрабатываем переменную функциями пользователя
                        // Получаем все аргументы кроме первых четырех
                        $funcs = array_slice(func_get_args(), 4);
                        foreach ($funcs as $func) {
                            if (is_callable($func)) $varValue = $func($varValue);
                        }
                    }
                    
                    return $varValue;
                }
            }
        }
        
        if (($argType == TP_ENUM) && is_array($argDefault) && count($argDefault) > 0) return $argDefault[0];
        else return $argDefault;
    }
    
    public function varExists($argName, $argScope = SC_GET, $caseSensitive = false) {
        if (!is_string($argName) || !is_integer($argScope) || strlen($argName) == 0) return NULL;

        for($i=0; $i<=SC_BIT_LENGHT; $i++) {
            // Проверяем по порядку все биты в $argScope (начиная со старшего)
            $bit = $argScope & (1<<$i);
            // Получаем источник данных
            if (isset($this->scopes[$bit])) {
                $scope = $this->scopes[$bit];
                // Проверяем наличие переменной в источнике с учетом регистра
                if ($this->arrayKeyExistsCS($argName, $scope, $caseSensitive)) return true;
            }
        }
        return false;
    }
    
    protected function arrayKeyExistsCS($keyToSearch, $array, $caseSensitive = false) {
        if (!$caseSensitive) return array_key_exists($keyToSearch, $array);
        else {
            foreach ($array as $key => $value) {
                if (strtolower($keyToSearch) == strtolower($key)) return true;
            }
        }
        return false;
    }
    
    // Привести к типу
    protected function applyType($type, $val, $default = '') {
        switch ($type) {
            case TP_ARRAY: $val = (array)$val; break;
            case TP_BOOL: $val = (bool)$val; break;
            case TP_CHAR:
                $val = (string)$val;
                if (strlen($val) > 0) $val = $val{0};
                else $val = $default;
            break;
            case TP_ENUM:
                if (!is_array($default) || (count($default) <= 0)) $val = NULL;
                else {
                    if (!in_array($val, $default)) $val = (string)$default[0];
                }
            break;
            case TP_FLOAT: $val = (float)$val; break;
            case TP_INT: $val = (int)$val; break;
            case TP_STRING: $val = (string)$val; break;
            case TP_UFLOAT:
                $val = (float)$val;
                if ($val < 0) $val = .0;
            break;
            case TP_UINT:
                $val = (int)$val;
                if ($val < 0) $val = 0;
            break;
            default:
                //trigger_error('Invalid parameter', E_USER_WARNING);
                $val = NULL;
            break;
        }
        return $val;
    }
    
    public function getConfigRelativePath() {
        return $this->configRelativePath;
    }
    
    public function isHTTPS() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off');
    }
    
    public function isMobile() {
		$result = null;
		if (class_exists('Mobile_Detect')) {
			$detect = new Mobile_Detect;
			$result = $detect->isMobile();
		}
        return $result;
    }
	
    public function isTablet() {
		$result = null;
		if (class_exists('Mobile_Detect')) {
			$detect = new Mobile_Detect;
			$result = $detect->isTablet();
		}
        return $result;
    }
    
    public function getSiteDomain() {
        return $_SERVER['HTTP_HOST'];
    }
    
    public function getSiteURL() {
        return $this->isHTTPS() ? 'https' : 'http' . '://' . $this->getSiteDomain() . '/';
    }
}

/*
function geQSParser_GetParamValue($paramName)
{
	global $ge_QueriedStruct;
	$res = false;
	if (isset($ge_QueriedStruct['Parameters']['Parameter']))
	{
		foreach ($ge_QueriedStruct['Parameters']['Parameter'] as $k => $v)
		{
			$e = '=';
			$paramValue = geCSV_GetLastVal($v,$e);
			if($paramValue==$v) $paramValue = '';
			$v = geCSV_GetFirstVal($v,$e);
			if($v == $paramName)
			{
				$res = $paramValue;
				break;
			}
		}
	}
	return $res;
}

function geQSParser_GetItemValue($itemName)
{
	global $ge_QueriedStruct;
	$res = false;
	if (isset($ge_QueriedStruct['Items']['Item']))
	{
		foreach ($ge_QueriedStruct['Items']['Item'] as $k => $v)
		{
			$e = '=';
			$itemValue = geCSV_GetLastVal($v,$e);
			if($itemValue==$v) $itemValue = '';
			$v = geCSV_GetFirstVal($v,$e);
			if($v == $itemName)
			{
				$res = $itemValue;
				break;
			}
		}
	}
	return $res;
}*/
