<?php
class register extends Module
{
	public $templateEngine = 'xslt';
	public $outputMode = MODULE_OUTPUT_NONE;
	
    public function getData($params = null) {
        $data = array();
        $data['sessionName'] = session_name();
        $data['sessionId'] = session_id();
        
        $step = $this->core->request->readVar('step', 1, SC_REQUEST, TP_INT);
        switch ($step) {
            case 1:
            break;
            case 2:
                $user = $this->core->request->readVar('user', array(), SC_REQUEST, TP_ARRAY);
                //if (empty($user['login'])) $this->error('Логин не может быть пустым!');
                if (empty($user['login'])) $user['login'] = $user['email'];
                if ($user['password'] !== $user['password2']) $this->error('Пароли не совпадают!');
                if ($this->isLoginUsed($user['login'])) $this->error('Указанный логин или email уже используется!');
                if (!Emails::valid($user['email'])) $this->error('Указан неверный email-адрес!');

                $captcha = $this->core->request->readVar('captcha', '', SC_REQUEST, TP_STRING);
                if (!(isset($_SESSION['captcha_keystring']) && ($_SESSION['captcha_keystring'] === $captcha))) $this->error('Неверное изображение с картинки!');
                
                if (!$this->stop) // Given data is correct
                {
                    $userId = $this->addUser($user);
                    $user['id'] = $userId;
                    if (!$userId) $this->error('Невозможно добавить пользователя!');
                    else {
                        $this->afterUserAdded($user);
						
                        $refererUrl = !empty($_SESSION['refererUrl']) ? $_SESSION['refererUrl'] : '';
						$registrationLogger = new Logger($this->core->getLogsPath(), 'regs-'.date("y-m-d").'.log');
						$registrationLogger->write("User {$user['login']} (id={$userId}) registered! Referer URL: $refererUrl");
                        
                        $auth = $this->core->getLoadedModuleObject('auth');
                        $auth->login($user['login'], $user['password']);
                    }
                }
                
                if ($this->stop) $step--;
            break;
            case 3:
                // blah-blah-blah...
                // All done! - Now do redirection to site root
                $this->core->redirectRelativeRoot();
            break;
            default:
            break;
        }
        
        $data['step'] = $step;
        return $data;        
	}
    
    public function getScripts() {
		return Array('/'.$this->core->request->siteRoot.$this->core->modulesPath.$this->getPath().'register.js');
	}
    
    public function isLoginUsed($login) {
        $auth = $this->core->getLoadedModuleObject('auth');
        $user = $this->db->selectOne('SELECT id FROM $@users WHERE login={1}', $auth->loginHash($login));
        return !empty($user);
	}
    
    public function addUser($user) {
        $auth = $this->core->getLoadedModuleObject('auth');
        $crypto_strong = true;
        $salt = openssl_random_pseudo_bytes(16, $crypto_strong);
        $toInsert = array();
        $toInsert['salt'] = utf8_encode($salt);
        $toInsert['password'] = $auth->passwordHash($user['password'], $salt, $user['login']);
        $toInsert['login'] = $auth->loginHash($user['login']);
        $toInsert['email'] = $user['email'];
        $toInsert['gender'] = $user['gender'];
        $toInsert['name'] = $user['name'];
        $toInsert['lastname'] = $user['lastname'];
        $toInsert['middlename'] = $user['middlename'];
        $toInsert['active'] = 1;
        $toInsert['type'] = USER_TYPE_USER;
		$insertClause = $this->db->buildInsertClause($toInsert);
		if ($this->db->query('INSERT INTO `$@users` '.$insertClause)) {
            $userId = $this->db->lastInsertId();
            return $userId;
        }
        else return false;
    }
    
    protected function afterUserAdded($user) {
        $res = false;
        if (!empty($user['email']) && Emails::valid($user['email'])) {
			$siteName = $this->core->request->getSiteDomain();
			$siteLink = 'http://'.$this->core->request->getSiteDomain();
			$fromAddress = 'noreply@'.$this->core->request->getSiteDomain();
            $body = 
"Поздравляем! <br/>\n
<br/>\n
Вы зарегистрированы на сайте <a href=\"$siteLink\">$siteName</a>!<br/>\n
Для входа, пожалуйста, используйте ваш логин: {$user['login']} <br/>\n
И ваш пароль: {$user['password']} <br/>\n
<br/>\n
С уважением, <a href=\"$siteLink\">$siteName</a> \n
";
            $res = Emails::send($fromAddress, $user['email'], "Регистраниц на сайте $siteName", $body);
        }
        return $res;
    }

}
