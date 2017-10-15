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

class RawAdapter extends BaseViewAdapter // implements ViewInterface
{
    public function render($data, $templateFile) {
        //var_dump($data);
        return $data;
    }
	
	public function getHeader() {
		return 'Content-type: text/html;';
		//return 'Content-type: plain/text;';
	}
}
