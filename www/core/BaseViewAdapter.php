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

abstract class BaseViewAdapter implements ViewInterface
{
    public $errorHandler = null;
    
    /*public function render($data, $templateFile) {
		return '';
    }*/
    
    public function __construct($errorHandler = null) {
		$this->errorHandler = $errorHandler;    
    }

    public function handleError($errorMessage, $errorCode = '', $errorDescription = '', $time = null) {
		if (!empty($this->errorHandler)) {
            call_user_func($this->errorHandler, $errorMessage, $errorCode, $errorDescription, $time);
        }
    }
	
	public function getHeader() {
		return '';
	}
}
