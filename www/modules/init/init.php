<?php
/*
    Модуль инициализации
    --------------------
*/

define('IT_ADVERT',     512);

define('ERR_ACCESS_DENIED',                 1);
define('ERR_NOT_ALLOWED_FOR_ANONYMOUS',     2);

function customAutoloader($className) {
    $filename = __DIR__.DS.'..'.DS.'..'.DS.'AbstractModules'.DS.$className.DS.$className.'.php';
    if (file_exists($filename)) include_once $filename;
    else {
        $filename = __DIR__.DS.'..'.DS.'..'.DS.'AdvancedClasses'.DS.$className.'.php';
        if (file_exists($filename)) include_once $filename;
    }
}

spl_autoload_register('customAutoloader');
Load::lib('debug_helper');

function customDBErrorCallback($message, $query) {
    global $core;
    $lenLimit = 1024;//112;
    if (strlen($query) > $lenLimit) $query = substr($query, 0, $lenLimit).'...';
    //$core->addError(utf8_encode($message.' (on running "'.$query.'" query)'));
    //$core->logger->write(utf8_encode($message.' (on running "'.$query.'" query)'));
    $core->logger->write($message.' (on running "'.$query.'" query)'.getTraceTxt(PHP_EOL.'TraceLog:'));
}

function setCustomDBErrorCallback() {
    global $db;
    $db->errorCallback = 'customDBErrorCallback';
}

setCustomDBErrorCallback();

require_once __DIR__.'/../../3rdParty/Mobile_Detect/Mobile_Detect.php';
