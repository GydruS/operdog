<?php
#################################
#       GydruS's Engine 3       #
#     "ErrorsKeeper" class      #
#             v. 1.0            #
#         2012 12 03-00         #
#################################

class ErrorsKeeper extends DataKeeper
{
    public $errors = null;
 
    public function __construct(){
        parent::__construct();
        $this->errors = &$this->items;
    }
    
    public function addError($errorMessage, $errorCode = '', $errorDescription = '', $time = null){
        $error['message'] = $errorMessage;
        $error['code'] = $errorCode;
        $error['description'] = $errorDescription;
        $error['time'] = empty($time) ? time() : $time;
        $this->add($error);
    }
}
