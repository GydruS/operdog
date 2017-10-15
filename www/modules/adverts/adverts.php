<?php
class adverts extends AbstractDictionary
{
    protected $tableName = 'adverts';
    public $doAfterGetItem = true;
	public $auth = null;
	public $imagesDictionary = null;
	public $categoriesDictionary = null;
	public $itemCookieExpire = 0;
	public $itemCookieTTL = 2592000; //86400 * 30; // 30 days;
	public $itemCookieName = 'userAdverts';
    public $previewTextMaxLength = 256;
    public $advertsSelectPeriod = 1209600; //86400 * 14; // 14 days
    private $activeItemHash = '';
    private $action = '';
    protected $order = 'DESC';
    protected $orderFields = 'id';
    
    public function __construct() {
        parent::__construct();
        $this->auth = $this->core->getLoadedModuleObject('auth');
        $this->itemCookieExpire = time() + $this->itemCookieTTL;
        $this->imagesDictionary = new DBDictionaryModule('images', 'id');
        $this->categoriesDictionary = new DBDictionaryModule('categories', 'id');
    }
    
    protected function getItemHash($item) {
		return md5($item['addedTimestamp'].$item['phone']);
	}
	
    protected function isValidPhone($phone) {
        $re = "/^\+{0,1}\d[\d\(\)\ -]{4,15}\d$/";
        return preg_match($re, trim($phone));
    }
    
    protected function isValidPhones($phones) {
        foreach ($phones as $phone) if (!$this->isValidPhone($phone)) return false;
        return true;
    }
    
    protected function processItemBeforeSave(&$item) {
		//if (empty($item['name'])) $this->error('Объявление не может быть без заголовка!');
		if (empty($item['phone'])) $this->error('Укажите телефон!');
        else {
            $phones = explode(',', $item['phone']);
            if (!$this->isValidPhones($phones)) $this->error('Укажите правильный телефон(ы)!');
        }
		if (!$this->stop) {
			if (empty($item['id'])) {
				$item['userCreatorId'] = (int)$this->core->getLoadedModuleObject('auth')->getUserId();
				$item['addedTimestamp'] = time();
				$item['active'] = 1;
				$item['hash'] = $this->getItemHash($item);
			}
            
            if (!empty($item['eventDate'])) {
                $re = "/\\d{2}\\.\\d{2}\\.\\d{4}/"; 
                if (preg_match($re, $item['eventDate'], $matches) > 0) {
                    $dateParts = explode('.',$item['eventDate']);
                    $item['eventDate'] = substr($dateParts[2], 2, 2).'-'.$dateParts[1].'-'.$dateParts[0];
                }
            }
            else $item['eventDate'] = '0000-00-00';
            
            if (empty($item['eventTime'])) $item['eventTime'] = '00:00';
            
            $item['eventDatetime'] = $item['eventDate'].' '.$item['eventTime'];//strtotime($item['date'].' '.$item['time']);
            
            unset($item['eventDate'], $item['eventTime']);
		}
        return !$this->stop;
    }
	
    public function getUserItemsHashes($autoincludeActiveItemHash = true) {
		$result = Array();
		if (!empty($_COOKIE[$this->itemCookieName])) $result = json_decode($_COOKIE[$this->itemCookieName], true);
		if (!empty($this->activeItemHash) && $autoincludeActiveItemHash) $result[] = $this->activeItemHash;
		return $result;
	}
	
    protected function afterItemSaved(&$item, $justAdded = false) {
		$item = $this->getItem($item['id']);
		$this->activeItemHash = $item['hash'];
		if ($justAdded) {
			$userItemsHashes = $this->getUserItemsHashes();
	        $cookieRes = setcookie($this->itemCookieName, json_encode($userItemsHashes), $this->itemCookieExpire, '/');
			$this->notice('Спасибо! Ваше объявление успешно добавлено. Мы надеемся, вы скоро найдете вашего друга!');
		}
		$this->saveImagesIfNeed($item['id']);
    }
	
	public function removeUserItemHashFromCookie($hash) {
		$hashes = $this->getUserItemsHashes(false);
		if (($key = array_search($hash, $hashes)) !== false) unset($hashes[$key]);
		$cookieRes = setcookie($this->itemCookieName, json_encode($hashes), $this->itemCookieExpire, '/');
		return true;
	}

	public function add(&$item) {
		unset($item['id']);
		$savedId = $this->saveItem($item);
		$item['id'] = $savedId;
		return !$this->stop;
	}
	
    public function basename($filePath) {return basename($filePath);}
	
    public function getAdminData($params = null) {return Array();}	// отключаем userType-based поведение родительского класса
	
    public function getUserData($params = null) {return Array();}	// отключаем userType-based поведение родительского класса
	
    public function getGuestData($params = null) {
        $action = $this->core->request->readVar('action', '', SC_REQUEST, TP_STRING);
        if (empty($action)) $action = $this->core->request->getParam(0);
        if (empty($action)) $action = 'list';
		
        switch ($action) {
			case 'view':
                $data['userAdverts'][$this->itemName] = $this->getUserAdverts();
			case 'edit':
			case 'print':
				$itemId = $this->core->request->getParam(1);
				$item = $this->getItem($itemId);
				if (empty($item)) $this->error('Запрошенное объявление не найдено!');
				else {
					$this->afterGetItem($item);
                    $data[$this->itemName] = $item;
                    if ($action == 'view') $this->increaseViewCounter($item);
                }
				if ($action == 'print') {
			        $this->templateFile = __DIR__.DIRECTORY_SEPARATOR.'advertsPrint.xsl';
					$this->outputMode = MODULE_OUTPUT_EXCLUSIVE;
					//$data['disableAutoPrint'] = true; // this line is for debug purposes
					//$data['disableAutoClose'] = true; // this line is for debug purposes
				}
			break;
			case 'delete':
				$itemId = $this->core->request->getParam(1);
				if (!$this->deleteItem($itemId)) $action = 'edit';
				else $action = 'list';
			break;
			case 'new':
			case 'save':
				$item = $this->core->request->readVar($this->itemName, Array(), SC_REQUEST, TP_ARRAY);
				if (!empty($item)) {
                    if (!empty($item['id'])) {
                        $result = $this->allowed($item['id']);
                        if ($result) $result = $this->saveItem($item);
                    }
                    else {
                        $result = $this->add($item);
                    }
                    
					if (!$result) {
						$data[$this->itemName] = $item;
					}
					else {
						$item = $this->getItem($item['id']);
						$this->afterGetItem($item);
						$action = 'view';
						if (!empty($item)) $data[$this->itemName] = $item;
						$data['justAdded'] = $action == 'new';
					}
				}
			break;
			case 'myAdverts':
				$data[$this->itemsName][$this->itemName] = $this->getUserAdverts();
				$data['userAdverts'][$this->itemName] = $data[$this->itemsName][$this->itemName];
			break;
			case 'deleteImage':
				$imageId = $this->core->request->getParam(1);
                if (!empty($imageId)) {
					$this->deleteItemImage($imageId, $itemId);
					$this->core->redirectRelativeRoot('adverts/edit/'.$itemId);
				}
            break;
            default:break;
        }
        
        $data['dictionaryInfo'] = $this->getDictionaryInfo();
        $data['action'] = $action;
		if ($action == 'list') {
			$perPage = $this->core->request->readVar('itemsPerPage', 10, SC_COOKIE, TP_INT);
			$page = $this->core->request->readVar('page', 1, SC_GET, TP_INT);
            $selectCriterias = new DBDictionaryCriterias(['addedTimestamp', 'active'], [time()-$this->advertsSelectPeriod, 1], ['>=', '='], 'AND');
			$data['navigation'] = $this->getPaginationData($selectCriterias);
	        $items = $this->getByCriterias(['addedTimestamp', 'active'], [time()-$this->advertsSelectPeriod, 1], ['>=', '='], 'AND', $this->idFieldName, 'DESC', $perPage * ($page-1), $perPage);
			foreach ($items as &$item) $this->afterGetItem($item);
			$data[$this->itemsName][$this->itemName] = $items;
			$data['userAdverts'][$this->itemName] = $this->getUserAdverts();
		}
        
        $data['categories']['category'] = $this->categoriesDictionary->getList();
		
		if (!empty($data[$this->itemsName][$this->itemName]) && !count($data[$this->itemsName][$this->itemName])) unset($data[$this->itemsName][$this->itemName]);
        
        $this->action = $action;
		
		return $data;
    }

    public function allowed($itemId) {
        $result = false;
        $item = $this->getItem($itemId);
        if (empty($item)) $this->error('Запрошенное объявление не найдено!');
        else {
            $userAdverts = $this->db->setIdsToArrayKeys($this->getUserAdverts());
            $result = array_key_exists($itemId, $userAdverts) || $this->core->getLoadedModuleObject('auth')->isAdmin();
            if (!$result) {
				//Load::lib('debug_helper');
				//trace();
				$this->error('У вас нет доступа к указанному объявдению!');
			}
        }
        return $result;
    }
    
    public function getUserAdverts() {
		$result = Array();
		$userItemsHashes = $this->getUserItemsHashes();
		if (!empty($userItemsHashes)) {
            $result = $this->getByCriterias(['active', 'hash'], [1, $userItemsHashes], ['=', 'IN']);
            foreach ($result as &$item) $this->afterGetItem($item);
        }
		return $result;
	}
    
    public function afterGetItem(&$item) {
        $item['previewText'] = $this->getItemPreviewText($item);
        $item['addedDate'] = date('d.m.Y', $item['addedTimestamp']);
        $eventDatetime = $item['eventDatetime'] != '0000-00-00 00:00:00' ? strtotime($item['eventDatetime']) : $item['addedTimestamp'];
        $item['eventDate'] = date('d.m.Y', $eventDatetime);
        $item['eventTime'] = date('H:i', $eventDatetime);
        $images = $this->getItemImages($item['id']);
        $item['images']['image'] = $images;
    }
    
    protected function deleteItem($itemId) {
		$result = false;
		if ($this->allowed($itemId)) {
			$item = $this->getItem($itemId);
			$this->beforeItemDelete($itemId);
			$result = $this->db->query('DELETE FROM `$@'.$this->tableName.'` WHERE `'.$this->idFieldName.'` = {1}', $itemId);
			if ($result) {
				$this->removeUserItemHashFromCookie($item['hash']);
				$this->notice('Объявление было успешно удалено!');
			}
		}
		return $result;
    }
	
    protected function beforeItemDelete($itemId) {
        $result = false;
        if ($this->allowed($itemId)) {
            // deleting images
            $images = $this->getItemImages($itemId);
            foreach ($images as $image) $this->deleteItemImage($image['id']);
            $result = true;
        }
        return $result;
    }
	
    public function deleteItemImage($imageId, &$itemId = 0) {
        $res = false;
        if (empty($imageId)) $imageId = $this->core->request->readVar('imageId', 0, SC_REQUEST, TP_INT);
        $image = current($this->imagesDictionary->getByCriteria('id', $imageId));
        if (!empty($image) && $this->allowed($image['itemId'])) {
			$itemId = $image['itemId'];
            $path = $this->getItemImagesDir($image['itemId'], false);
            $fileName = $path.'/'.$this->basename($image['path']);
            unlink($fileName);
            $res = $this->imagesDictionary->deleteItem($image['id']);
        }
        return $res;
    }

    public function getScripts() {
		$scripts = array();
        if ($this->action == 'print') {
            $scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/jquery.js';
            $scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/common.js';
            $scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.'common/qrcode.js';
            $scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.$this->getPath().'advertsPrint.js';
        }
        else {
            $scripts[] = '/'.$this->core->request->siteRoot.$this->core->modulesPath.$this->getPath().$this->getName().'.js';
        }
        return $scripts;
    }

    public function getStyles() {
		$styles = array('/'.$this->core->request->siteRoot.$this->core->modulesPath.$this->getPath().$this->getName().'.css');
		return $styles;
	}
    
	public function getItemPreviewText($item) {
        $len = $this->previewTextMaxLength;
        $descr = mb_strlen($item['text'], 'utf-8') > $len ? mb_substr($item['text'], 0, $len-3, 'utf-8').'...' : $item['text'];
        return strip_tags($descr);
    }
    
	public function increaseViewCounter($item) {
        if (!empty($item['id'])) {
            $sql = 'UPDATE `$@'.$this->tableName.'` SET viewsCount = viewsCount + 1 WHERE `'.$this->idFieldName.'` = {1}';
            $this->db->query($sql, $item['id']);
        }
    }
	
    protected function getItemImagesDir($itemId, $relative = true) {
        $path = $relative ? 'files/advert'.$itemId : __DIR__.'/../../files/advert'.$itemId;
        return $path;
    }
    
    public function getItemImages($itemId) {
        $this->imagesDictionary->selectCriterias = new DBDictionaryCriterias('itemId', $itemId);
        $images = $this->imagesDictionary->getOrderedList('number', 'ASC');
        foreach ($images as $key => $image) $images[$key]['creationDate'] = date('m.d.Y', strtotime($image['creationDatetime']));
        return $images;
    }
	
    public function isFileImage($fileName) {
        $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG);//, IMAGETYPE_GIF);
        $detectedType = exif_imagetype($fileName);
        return in_array($detectedType, $allowedTypes);
    }
    
	public function saveImagesIfNeed($itemId) {
        if ($this->allowed($itemId) && !empty($_FILES['itemImages'])) {
            $path = $this->getItemImagesDir($itemId, false);
            $postedFiles = &$_FILES['itemImages'];
            foreach ($postedFiles['name'] as $key => $name) {
                if (!empty($name)) {
                    if (!file_exists($path)) if (!mkdir($path)) $this->error("Не удалось создать директорию для хранения файлов пользователя!"); //Dir $path Cannot be created!
                    $tmpFileName = $postedFiles['tmp_name'][$key];
                    if (!$this->isFileImage($tmpFileName)) $this->error("Загруженный файл $name не является изоражением!"); //Uploaded file $name is not an image!

                    if (!$this->core->errorsCount()) {
                        $fileName = geGetNewFilenameIfFileExists($path.'/'.geCorrectFileName($name));
                        if(!move_uploaded_file($tmpFileName, $fileName)) $this->error("Не удалось сохранить загруженный файл $name!");
                        else {
                            // add to DB
                            $relativePath = $this->getItemImagesDir($itemId);
                            $relativeFileName = $relativePath.'/'.$this->basename($fileName);
                            $title = $this->core->request->readVar('title', '', SC_REQUEST, TP_STRING);
                            $description = $this->core->request->readVar('description', '', SC_REQUEST, TP_STRING);
                            $creationDatetime = $this->core->request->readVar('creationDatetime', '0000-00-00 00:00:00', SC_REQUEST, TP_STRING);
                            $addedImageRecord = $this->addImageRecordToDBDictionary($itemId, $relativeFileName, $title, $description, $creationDatetime);
                        }
                    }

                    // Что-то пошло не так. Подчищаем.
                    if ($this->core->errorsCount()) {
                        unlink($tmpFileName);
                    }
                }
            }
        }
    }
	
    public function addImageRecordToDBDictionary($itemId, $fileName, $title = '', $description = '', $creationDatetime = '0000-00-00 00:00:00') {
        $path = $this->getItemImagesDir($itemId, false);
        $fullFileName = $path.'/'.$this->basename($fileName);
        
        $filesize = filesize($fullFileName);
        list($width, $height) = getimagesize($fullFileName);
        $imageRecord = Array(
            'path' => $fileName,
            'width' => $width,
            'height' => $height,
            'number' => 0,
            'filesize' => $filesize,
            'name' => $title,
            'description' => $description,
            'itemId' => $itemId,
            'time' => time(),
            'creationDatetime' => $creationDatetime,
        );
        $imageRecord['id'] = $this->imagesDictionary->addItem($imageRecord);
        return $imageRecord;
    }
}
