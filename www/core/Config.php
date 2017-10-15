<?php
#################################
#       GydruS's Engine 3       #
#        "Config" class         #
#            v. 1.1             #
#    2012 11 24 - 2014 01 03    #
#################################

class Config
{
    public $fileName = '';
    public $data = null;
    public $errorCallback = null;
    private $fileHandle = null;
    private $mode = 'w+';//'cb';   <-- Нужно использовать w+ т.к. cb не перетрет остатки файла, если новый записываемый файл окажется короче, чем был раньше!
    private $needWriteToFile = false;
 
    public function __construct($fileName, $errorCallback = null) { // = '../config.php'){
        $this->errorCallback = $errorCallback;
        $this->data = array();
        $this->readFromFile($fileName);
    }
    
    public function __destruct() {
        $this->finalize();
    }
    
    public function finalize() {
        if ($this->needWriteToFile) $this->writeToFile();
        $this->closeHandleIfOpened();
        $this->needWriteToFile = false;
    }

    public function write($name, $value) {
        $this->needWriteToFile = true;
        $this->data[$name] = $value;
    }

    public function read($name, $default = null) {
        return isset($this->data[$name]) ? $this->data[$name] : $default;
    }
    
    public function readFromFile($fileName) {
        $this->fileName = $fileName;
        if(file_exists($fileName)) {
            $configArrayName = $this->getConfigArrayName();
            require $fileName;
            if(!empty($$configArrayName)) $this->data = $$configArrayName;
            else $this->data = array();
        }
    }

    public function writeToFile($fileName = '', $mode = '') {
        if (empty($fileName)) $fileName = $this->fileName;
        if (empty($mode)) $mode = $this->mode;
        $this->closeHandleIfOpened();
        $this->fileHandle = fopen($fileName, $mode);
        if (!$this->fileHandle) $this->callErrorCallback("Unable to open \"$fileName\" in \"$mode\" mode!");
        else {
            $this->writeString("<?php");
            foreach ($this->data as $key => $value) {
                $this->writeVariable($key, $value);
            }
            $this->writeString("?>");
        }
        $this->closeHandleIfOpened();
    }
    
    private function closeHandleIfOpened() {
        if (!empty($this->fileHandle)) {
            $res = fclose($this->fileHandle);
            if ($res) {
                $this->fileHandle = null;
                return $res;
            }
        }
        else return true;
    }

    protected function writeVariable($variable, $value) {
        if (!empty($this->fileHandle) && (!empty($variable))) {
            $strToWrite = $this->buildVarValPair($variable, $value);
            if (!empty($strToWrite)) return fwrite($this->fileHandle, $strToWrite);
        }
        return false;
    }

    protected function buildVarValPair($variable, $value) {
        if (!empty($variable)) {
            $variable = '$'.$this->getConfigArrayName().'[\''.$variable.'\']';
            if (is_string($value)) $value = "'".str_replace(array("\\", "'"), array("\\\\", "\'"), $value)."'";
            if (is_bool($value)) $value = $value ? 'true' : 'false';
            if (is_null($value)) $value = 'null';
            $result = "\t".$variable.' = '.$value.";\r\n";
            return $result;
        }
        return false;
    }
    
    protected function writeString($string) {
        if (!empty($this->fileHandle) && ($string !== '')) return fwrite($this->fileHandle, $string."\r\n");
        else return false;
    }

    protected function writeComment($comment) {
        return $this->writeString('/* '.$comment.' */');
    }
    
    protected function getConfigArrayName() {
        return $this->buildConfigArrayName($this->fileName);
    }
    
    protected function buildConfigArrayName($fileName) {
        $fileName = $this->correctFileName($fileName);
        $arrayName = geCSV_GetFirstVal(geCSV_GetLastVal($fileName, '/', false), '.').'Data';
        return $arrayName;
    }
    
    protected function correctFileName($fileName) {
        $fileName = str_replace('\\', '/', $fileName);
        $fileName = preg_replace("/[\\'&\s@\":=#]+/","_", $fileName);
        return $fileName;
    }
    
    protected function callErrorCallback($message) {
        if (!empty($this->errorCallback)) call_user_func($this->errorCallback, $message);
    }
}
