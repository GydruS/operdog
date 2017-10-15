<?php

define('USER_TYPE_GUEST', 0);
define('USER_TYPE_USER',  1);
define('USER_TYPE_ADDER', 2);
define('USER_TYPE_MODERATOR', 6);
define('USER_TYPE_ADMIN', 7);

class auth extends Module {
    protected $userInfo = Array();
    public $outputMode = MODULE_OUTPUT_NONE;
    public $provideStyles = FALSE;
    public $provideScripts = FALSE;
    public $provideContext = FALSE;
    public $authCookieName = 'auth_token';
    public $authCookieExpire = 0;
    public $allowMultiLogins = false;
    
    public function __construct() {
        global $core, $db;
        $this->authCookieExpire = time() + (86400 * 365); // 86400 = 1 day
        $this->core = $core;
        $this->db = $db;
        if (session_status() == PHP_SESSION_NONE) session_start();
        $this->init();
        parent::__construct();
    }
    
    public function init() {
        $this->logoutIfNeed();
        $this->loginIfNeed();
        $this->loginByCookieIfNeed();
        $this->updateUserInfo();
    }
    
    public function logoutIfNeed() {
        $logout = $this->core->request->readVar('logout', false, SC_REQUEST, TP_BOOL);
        if ($logout) $this->logout();
	}
    
    public function logout() {
        $id = $this->getUserId();
        if ($id) {
            $user = $this->getUser();
            $this->db->query('UPDATE $@users SET token = {1} WHERE id = {2}', $this->generateAuthToken($user), $user['id']);
        }
        if (!empty($_COOKIE[$this->authCookieName])) {
            if ($this->allowMultiLogins) $this->closeAuthSession($_COOKIE[$this->authCookieName]);
        }
        $this->removeAuthCookie();
        $this->user = null;
        unset($_SESSION['user']);
    }
    
    public function loginIfNeed() {
        $login = $this->core->request->readVar('login', '', SC_POST, TP_STRING);
        if (!empty($login)) {
            $password = $this->core->request->readVar('password', '', SC_POST, TP_STRING);
            $this->login($login, $password);
        }
	}
    
    public function login($login, $password, $updateSession = true) {
        $result = false;
        $loginHash = $this->loginHash($login);
        $user = $this->db->selectOne('SELECT * FROM $@users WHERE active = 1 AND login = {1}', $loginHash);
        if (!empty($user)) {
            $salt = utf8_decode($user['salt']);
            $passwordHash = $this->passwordHash($password, $salt, $login);
            if ($passwordHash == $user['password']) {
                $result = true;
                $this->onSuccessLogin($user, $updateSession, true);
            }
            else $this->error('Неверный пароль или имя пользователя!');
        }

        if (empty($user)) {
            $this->error('Неверный пароль или имя пользователя!'); // wrong login, if describe more accurately
            $this->logout();
        }
        return $result;
	}
    
    public function loginByCookieIfNeed() {
        if (!empty($_COOKIE[$this->authCookieName]) && empty($_SESSION['user'])) {
            $this->loginByToken($_COOKIE[$this->authCookieName]);
        }
    }
    
    public function loginByToken($token) {
        if (!$this->allowMultiLogins) $user = $this->db->selectOne('SELECT * FROM $@users WHERE active = 1 AND token = {1}', $token);
        else {
            $authSession = $this->getAuthSession($token);
            if (!empty($authSession)) {
                $user = $this->db->selectOne('SELECT * FROM $@users WHERE active = 1 AND id = {1}', $authSession['userId']);
            }
        }
        
        if (!empty($user)) {
            $this->onSuccessLogin($user);
        }
    }
    
    private function onSuccessLogin($user, $updateSession = true, $startNewAuthSession = false) {
        $authCookieVal = $this->setAuthCookie($user);
        $this->cleanupUserArray($user);
        $ip = $this->getUserIP();
        $this->db->query('UPDATE $@users SET lastLogin = {1}, loginsCount = loginsCount + 1, token = {2}, `ip` = {3} WHERE id = {4}', time(), $authCookieVal, $ip, $user['id']);
        $user['advancedData'] = json_decode($user['advancedData'], true);
        if ($updateSession) $_SESSION['user'] = $user;
        if ($this->allowMultiLogins && $startNewAuthSession) {
            $this->startNewAuthSession($user['id'], $authCookieVal, $ip);
        }
    }
    
    private function startNewAuthSession($userId, $token, $ip, $sessionTTL = 31536000) { // 31536000 = 365 days
        $newAuthSession = Array(
            'userId' => $userId,
            'token' => $token,
            'started' => date("Y-m-d H:i:s"),
            'validTo' => date("Y-m-d H:i:s", time()+$sessionTTL),
            'ip' => $ip,
        );
		$insertClause = $this->db->buildInsertClause($newAuthSession);
		$result = $this->db->query('INSERT INTO $@authSessions '.$insertClause);
        return $result;
    }
    
    public function closeUserAuthSessions($userId) {
		$result = $this->db->query('UPDATE $@authSessions SET validTo = {1} WHERE userId = {2}', date("Y-m-d H:i:s"), $userId);
        return $result;
    }
    
    public function getAuthSession($token) {
        $authSession = $this->db->selectOne('SELECT * FROM $@authSessions WHERE token = {1} AND validTo > {2} ORDER BY id DESC LIMIT 1', $token, date("Y-m-d H:i:s"));
        return $authSession;
    }
    
    public function closeAuthSession($token) {
        $result = false;
        $authSession = $this->getAuthSession($token);
        if (!empty($authSession)) {
            $result = $this->db->query('UPDATE $@authSessions SET validTo = {1} WHERE id = {2}', date("Y-m-d H:i:s"), $authSession['id']);
        }
        return $result;
    }
    
    protected function setAuthCookie($user) {
        $authCookieVal = $this->generateAuthToken($user);
        setcookie($this->authCookieName, $authCookieVal, $this->authCookieExpire, '/');
        return $authCookieVal;
    }
    
    protected function removeAuthCookie() {
        setcookie($this->authCookieName, null, -1, '/');
        unset($_COOKIE[$this->authCookieName]);
    }
    
    public function setUserAdvancedData($advancedData) {
        $id = $this->getUserId();
        if ($id && $this->db->query('UPDATE $@users SET advancedData = {1} WHERE id = {2}', json_encode($advancedData), $id)) {
            $this->userInfo['advancedData'] = $advancedData;
            $_SESSION['user']['advancedData'] = $advancedData;
            return true;
        }
        return false;
    }
    
    public function getUserAdvancedData() {
        $user = $this->getUser();
        if (!empty($user) && !$this->isGuest()) {
            return $user['advancedData'];
        }
        return Array();
    }
    
    public function setUserTimezone($newTimezone) {
        $id = $this->getUserId();
        if ($id && $this->db->query('UPDATE $@users SET timezone = {1} WHERE id = {2}', $newTimezone, $id)) {
            $this->userInfo['timezone'] = $newTimezone;
            $_SESSION['user']['timezone'] = $newTimezone;
            return true;
        }
        return false;
    }
    
    public function getUserTimezone($defaultTimezone = 'Europe/Moscow') {
        $user = $this->getUser();
        if (!empty($user) && !$this->isGuest()) {
            Load::lib('date_helper');
            if (isTimezoneCorrect($user['timezone'])) return $user['timezone'];
        }
        return $defaultTimezone;
    }
    
    protected function generateAuthToken($user) {
        return $this->hash(time().$user['salt'].$user['login']);
    }
    
    public function refreshUserData() {
        $id = $this->getUserId();
        if ($id) {
            $user = $this->db->selectOne('SELECT * FROM $@users WHERE id={1}', $id);
            $this->cleanupUserArray($user);
            $user['advancedData'] = json_decode($user['advancedData'], true);
            $_SESSION['user'] = $user;
            $this->updateUserInfo();
        }
	}
     
    private function cleanupUserArray(&$user) {
        unset($user['password'], $user['salt']);
    }
    
    public function updateUserInfo() {
        if (!empty($_SESSION['user'])) {
            $this->userInfo = $_SESSION['user'];
            $this->db->query("UPDATE $@users SET `lastActivity` = {1} WHERE `id` = {2}", time(), $this->userInfo['id']);
        }
        else $this->userInfo['type'] = USER_TYPE_GUEST;
    }
    
    public function passwordHash($pwd, $salt, $login) {
        # pass_hash = sha1(salt.sha1(salt.username).sha1(pass))
        return $this->hash($salt.$this->hash($salt.$login).$this->hash($pwd));
	}
    
    public function loginHash($login) {
        return $login;//$this->hash($login);
	}
    
    public function hash($val) {
        return sha1($val);
	}
    
    protected function returnJSON($data) {
        header('text/html; charset=utf-8');
        echo json_encode($data);
        die(0);
    }

    protected function returnJSONError($errors = null) {
        $errorAnswer = $this->getJSONErrorAnswer($errors);
        $this->returnJSON($errorAnswer);
    }
    
    protected function getJSONErrorAnswer($errors = null) {
		if (empty($errors)) $errors = $this->core->getErrors();
        if (gettype($errors) == 'string') $errors = Array($errors);
		$lastError = end($errors);
        $res = array();
        $res['result'] = false;
        $res['errors'] = $errors;
        $res['lastErrorCode'] = $lastError ? $lastError['code'] : '';
        return $res;
    }
    
    public function getData($params = null) {
        if (!empty($this->userInfo))  $userInfo = $this->userInfo;
        else $userInfo = null;
        
        $action = $this->core->request->getParam(0);
        if ($action == 'login') {
            $login = $this->core->request->readVar('login', '', SC_POST, TP_STRING);
            $password = $this->core->request->readVar('password', '', SC_POST, TP_STRING);
            $this->login($login, $password);
        
            if ($this->stop) $this->returnJSONError();
            else $this->returnJSON(Array('result' => true));
        }
        
		return $userInfo;
	}
    
    public function getUser() {
		return $this->getData();
	}
    
    public function getUserId() {
        if (!empty($this->userInfo['id'])) return $this->userInfo['id'];
        else return null;
	}
	
    public function getUserLogin() {
        if (!empty($this->userInfo['login'])) return $this->userInfo['login'];
        else return '';
	}
    
    public function isGuest() {
		return (empty($this->userInfo['type']) || ((int)$this->userInfo['type'] === USER_TYPE_GUEST));
	}
    
    public function isAdmin() {
		return (!empty($this->userInfo['type']) && ((int)$this->userInfo['type'] === USER_TYPE_ADMIN));
	}
    
    public function getUserType() {
		if (!empty($this->userInfo['type'])) return $this->userInfo['type'];
        else return USER_TYPE_GUEST;
	}
       
    /*public function generatePassword() {
    	return bin2hex(openssl_random_pseudo_bytes(12));
    }*/
    
    public function isValidEmail($address) {
        return (filter_var($address, FILTER_VALIDATE_EMAIL) !== false);
    }
    
    public function getUserIP() {
        $ipAddress = '';
        if (getenv('HTTP_CLIENT_IP'))
         $ipAddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
         $ipAddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
         $ipAddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
         $ipAddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
        $ipAddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
         $ipAddress = getenv('REMOTE_ADDR');
        else
         $ipAddress = '';//'UNKNOWN';

        return $ipAddress; 
    }
    
}