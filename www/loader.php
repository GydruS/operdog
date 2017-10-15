<?php

//namespace gethree;

function gethreeAutoloader($class_name) {
	$filename = 'core/'.$class_name.'.php';
	if (!file_exists($filename)) $filename = 'core/classes/'.$class_name.'.php';
	if (!file_exists($filename)) $filename = 'core/interfaces/'.$class_name.'.php';
	if (file_exists($filename)) include_once $filename;
	else /*log here!!!*/;
}

spl_autoload_register('gethreeAutoloader');

class Load //extends Singleton
{
    public static function lib($name) {
		include_once 'lib/'.$name.'.php';
    }
	
    public static function dbAdapter($name) {
		$filename = 'core/db_adapters/'.$name.'.php';
		return Load::file($filename);
    }
	
    public static function cacheAdapter($name) {
		$filename = 'core/cache_adapters/'.$name.'.php';
		return Load::file($filename);
    }
	
    public static function viewAdapter($name) {
		$filename = 'core/view_adapters/'.$name.'.php';
		return Load::file($filename);
    }
	
    public static function module($path, $name, $modulesPath = 'modules/') {
		$filename = $modulesPath.$path.$name.'.php';
		return Load::file($filename);
    }
	
    public static function file($filename) {
#		echo 'Loading: '.$filename;
		if (!file_exists($filename)) {
#            echo ' ... Failed!!!<br/>';
            return false;
        }
#		echo ' ... Ok!<br/>';
		include_once $filename;
//		echo $mainSelfRendering;
		return true;
    }
}


?>
