<?php
#################################
#       GydruS's Engine 3       #
#     "NoticesKeeper" class     #
#             v. 1.0            #
#         2013 01 16-00         #
#################################

class NoticesKeeper extends DataKeeper
{
    public $notices = null;
 
    public function __construct(){
        parent::__construct();
        $this->notices = &$this->items;
    }

    public function addNodice($message, $header = '', $group = '', $time = null){
        $notice['message'] = $message;
        $notice['header'] = $header;
        $notice['group'] = $group;
        $notice['time'] = empty($time) ? time() : $time;
        $this->add($notice);
    }
}
