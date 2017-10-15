<?php
#################################
#       GydruS's Engine 3       #
#      "phpAdapter" class       #
#             v. 1.0            #
#	       2012 10 10           #
#################################

#################################
# Description
#--------------------------------
#
#

class JSONAdapter extends BaseViewAdapter // implements ViewInterface
{
	public $useCustomJsonConvertor = false;
	
    public function render($data, $templateFile) {
		if ($this->useCustomJsonConvertor) {
			$a2json = new Array2JSON();
			$json = $a2json->convert($data);
		}
		else $json = json_encode($data);
        return isset($_GET['callback']) ? "{$_GET['callback']}($json)" : $json;
    }
	
	public function getHeader() {
        //header('text/html; charset=utf-8');
		//return 'Content-type: application/json;';
		return 'Content-type: application/json; charset=utf-8';
		//return 'Content-type: plain/text;';
	}
}
