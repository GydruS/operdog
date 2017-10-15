<?php
#################################
#       GydruS's Engine 3       #
#     "FilesComposer" class     #
#             v. 1.0            #
#         2016 12 22-00         #
#################################

class FilesComposer
{
    public $cachePath = ''; //__DIR__.DS.'../cache/';
 
    public function __construct($cachePath = null) {
		if ($cachePath !== null) $this->cachePath = $cachePath;
        else $this->cachePath = __DIR__.DS.'../cache/';
    }

    public function getHash($files) {
		$hash = md5(implode('', $files));
		return $hash;
	}
	
    public function cache($files, $cachedFileName) {
        $eol = "\r\n";//PHP_EOL;
		$fileHandle = fopen($cachedFileName, 'w');
		foreach ($files as $file) {
			$fileContent = file_get_contents($_SERVER['DOCUMENT_ROOT'].$file);
			fwrite($fileHandle, $fileContent.$eol);
		}
		$res = fclose($fileHandle);
		return $res;
	}
	
    public function compose($files, $resultExtension = '.js'){
		$cachedFileName = $this->cachePath.$this->getHash($files).$resultExtension;
		if (!file_exists($cachedFileName)) $this->cache($files, $cachedFileName);
        return $cachedFileName;
    }
}
