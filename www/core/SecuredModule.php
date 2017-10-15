<?php
#################################
#       GydruS's Engine 3       #
#     "SecuredModule" class     #
#             v. 1.0            #
#           2012 12 12          #
#################################

#################################
# Description
#--------------------------------
#
#

class SecuredModule extends Module
{
    //public $guestAccessMethods = Array('getGuestData');
    //public $userAccessMethods = Array('getUserData');
    //public $adminAccessMethods = Array('getAdminData');
//    public $userUserTypes = Array(USER_TYPE_ADMIN);
//    public $adminUserTypes = Array(USER_TYPE_ADMIN);
    
    public final function getData($params = null) {
        //$data = array();
        $data = parent::getData($params);
        $data['dictionaryInfo']['moduleName'] = get_class($this);
        $auth = $this->core->getLoadedModuleObject('auth');
        $userType = $auth->getUserType();
        switch ($userType) {
            case USER_TYPE_GUEST:
                $data = array_merge($data, $this->getGuestData($params));
            break;
            case USER_TYPE_USER:
            case 2:
                $guestData = $this->getGuestData($params);
                $params['guestData'] = $guestData;
                $data = array_merge($data, $guestData, $this->getUserData($params));
            break;
            case USER_TYPE_ADMIN:
                $guestData = $this->getGuestData($params);
                $params['guestData'] = $guestData;
                $userData = $this->getUserData($params);
                $params['userData'] = $userData;
                $data = array_merge($data, $guestData, $userData, $this->getAdminData($params));
            break;
        }
        return $data;
	}
    
    protected function getGuestData($params = null) {
		return Array();
	}
    
    protected function getUserData($params = null) {
		return Array();
	}
    
    protected function getAdminData($params = null) {
		return Array();
	}
	
}
