<?php
#################################
#       GydruS's Engine 3       #
#        "Logger" class         #
#             v. 1.0            #
#         2012 11 26-00         #
#################################

class Logger
{
    public $logsDir = '';
    private $fileName = '';
    private $fileHandle = null;
    private $mode = 'a';
    private $addCallStack = false;
    private $callStackArgumentMaxLength = 32; // 0 for unlimited;
    private $addRequestString = false;
 
    public function __construct($logsDir, $fileName = '', $addCallStack = false, $addRequestString = false, $callStackArgumentMaxLength = 32) {
        $lastChar = $logsDir[strlen($logsDir)-1];
        if (($lastChar !== '/') && ($lastChar !== '\\')) $logsDir .= '/';
        $this->logsDir = $logsDir;
        $this->fileName = $fileName;
        $this->addCallStack = $addCallStack;
        $this->callStackArgumentMaxLength = $callStackArgumentMaxLength;
        $this->addRequestString = $addRequestString;
    }
    
    public function __destruct() {
        $this->closeHandleIfOpened();
    }

    public function write($logString, $addDatetime = true, $addCallStack = null, $addRequestString = null) {
        $eol = "\r\n";//PHP_EOL;
        if (empty($this->fileHandle)) $this->openFile();
        if ($addDatetime) $logString = date("Y-m-d H:i:s")."\t".$logString;
		if ($addRequestString || (is_null($addRequestString) && $this->addRequestString)) $logString .= $eol.'Request string: '.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		if ($addCallStack || (is_null($addCallStack) && $this->addCallStack)) $logString .= $eol.'Calls:'.$eol.$this->getTraceTxt(2).$eol;
        return fwrite($this->fileHandle, $logString.$eol);
    }
    
    private function openFile($mode = '') {
        if (empty($this->fileName)) $this->fileName = date("Y-m-d").'.log';
        if (empty($mode)) $mode = $this->mode;
        $this->fileHandle = fopen($this->logsDir.$this->fileName, $mode);
    }
    
    public function closeHandleIfOpened() {
        if (!empty($this->fileHandle)) {
            $res = fclose($this->fileHandle);
            if ($res) {
                $this->fileHandle = null;
                return $res;
            }
        }
        else return true;
    }
	
    public function getTraceTxt($skipCalls = 1, $traceLineBreak = PHP_EOL) {
        $calls = debug_backtrace();
        $l = count($calls);
        $result = '';

        for ($i = $l-1; $i > $skipCalls-1; $i--) { // not >= 0, cause $calls[0] is this function (trace())! we don't need to output this!
            $call = $calls[$i];
            $callNumber = $l-$i;
            if ($callNumber < 10) $callNumber = '0'.$callNumber;
            $s = $callNumber.":\t";
            if (!empty($call['class'])) $s .= $call['class'].' ';
            if (!empty($call['type'])) $s .= $call['type'].' ';
            $s .= $call['function'].'('.$this->argsToStr($call['args']).')';
            $file = !empty($call['file']) ? $call['file'] : '?';
            $line = !empty($call['line']) ? $call['line'] : '?';
            $s = $s."\t\t".$file.':'.$line;
            $result .= $s.$traceLineBreak;
        }
        
        return $result;
    }

	protected function argsToStr($args) {
        $argsToProcess = $this->cloneArgsArray($args);
        array_walk($argsToProcess, array($this, 'debugLoggerArrayWalker'));
        return implode(', ', $argsToProcess);
    }
	
    protected function cloneArgsArray($args) {
        $newArray = array();
        foreach($args as $key => $value) {
            if(is_array($value)) $newArray[$key] = array_merge($value);
            else if(is_object($value)) $newArray[$key] = clone $value;
            else $newArray[$key] = $value;
        }
        return $newArray;
    }
    
    public function debugLoggerArrayWalker(&$item, $key) {
        
        if (is_null($item)) $item = 'null';
        elseif (is_bool($item)) $item = $item ? 'true' : 'false';
        elseif (is_string($item)) $item = '\''.$this->cutIfNeed($item).'\'';
        elseif (is_array($item)) $item = 'Array('.$this->cutIfNeed(serialize($item)).')';
        elseif (is_object($item)) $item = 'Object:'.get_class($item).'('.$this->cutIfNeed(serialize($item)).')';
    }
    
    private function cutIfNeed($str) {
        $maxLen = $this->callStackArgumentMaxLength;
        if ($maxLen > 0) {
            $size = $this->getSize($str);
            if ($maxLen < $size) $str = substr($str, 0, $maxLen).'...';
        }
        return $str;
    }

    private function getSize($serializedItem) {
        if (function_exists('mb_strlen')) $size = mb_strlen($serializedItem, '8bit');
        else $size = strlen($serializedItem);
        return $size;
    }

}
