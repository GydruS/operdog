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

class XMLAdapter extends BaseViewAdapter // implements ViewInterface
{
    public function render($data, $templateFile) {
		if(!is_array($data)) $data = Array($data);
		$converter = new Array2XML();
		return $converter->convert($data);
    }
	
	public function getHeader() {
		return 'Content-type: text/xml;';
		//header('Content-type: text/xml; charset=UTF-8');
	}
}
