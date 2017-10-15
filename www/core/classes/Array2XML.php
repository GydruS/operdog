<?php

class Array2XML
{
    private $writer;
    private $version = '1.0';
    private $encoding = 'utf-8';
    private $rootName = 'document';
    
 
    function __construct() {
        $this->writer = new XMLWriter();
    }
    
    public function convert($data) {
        $this->writer->openMemory();
        $this->writer->startDocument($this->version, $this->encoding);
        $this->writer->startElement($this->rootName);
        if (is_array($data)) {
			//ge_ClearArray($data);
            $this->getXML($data);
        }
        $this->writer->endElement();
        return $this->writer->outputMemory();
    }
    public function setVersion($version) {
        $this->version = $version;
    }
    public function setEncoding($encoding) {
        $this->encoding = $encoding;
    }
    public function setRootName($rootName) {
        $this->rootName = $rootName;
    }
    public function encode($val) {
        //if ($this->encoding == 'utf-8') $val = utf8_encode($val);
        return $val;
    }
    
    private function getXML($data, $parentKeyName = NULL, $parentCount = 0) {
        foreach ($data as $key => $val)
		{
			$numeric = is_numeric($key);
#			echo "<br /> key=$key; val=$val; parentKeyName=$parentKeyName; parentCount=$parentCount; numeric=$numeric; ";
            if (is_array($val)) 
			{
				if ($numeric)
				{
					$parentKeyName == NULL ? $keyName = 'key'.$key : $keyName = $parentKeyName;
					$this->getXML($val, $parentKeyName);
					if ($key<$parentCount-1)
					{
#						echo " endElement(); 1";
						$this->writer->endElement();
#						echo " startElement($parentKeyName); 1";
						$this->writer->startElement($parentKeyName);
					}
				}
				else
				{
					$keyName = $key;
#					echo " startElement($keyName); 2";
					$this->writer->startElement($keyName);
					$this->getXML($val, $keyName, count($val));
#					echo " endElement(); 2";
					$this->writer->endElement();
				}
			}
            else
			{
				if ($numeric)
				{
					$parentKeyName == NULL ? $keyName = 'key'.$key : $keyName = $parentKeyName;
					if ($key<($parentCount-1))
					{
#						echo " writeRaw($val) 3";
						$this->writer->writeRaw($this->encode($val));
#						echo " endElement(); 3";
						$this->writer->endElement();
#						echo " startElement($keyName); 3";
						$this->writer->startElement($keyName);
					}
					else
					{
#						echo " writeRaw($val) 4";
						$this->writer->writeRaw($this->encode($val));
					}
				}
				else
				{
					$keyName = $key;
#					echo " writeElement($keyName, $val) 3";
                    if (gettype($val) === 'object') $val = '[Object]';
//                    else $val = filter_var($val,FILTER_SANITIZE_SPECIAL_CHARS);
					$this->writer->writeElement($keyName, $this->encode($val));
				}
			}
        }
    }
    /*private function getXML($data) {
        foreach ($data as $key => $val) {
            if (is_numeric($key)) {
                $key = 'key'.$key;
            }
            if (is_array($val)) {
                $this->writer->startElement($key);
                $this->getXML($val);
                $this->writer->endElement();
            }
            else {
                $this->writer->writeElement($key, $val);
            }
        }
    }*/
}
