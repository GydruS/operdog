<?php 

class Cookie{

    static $prefix = 'gethree_';

    public static function set($key, $val, $expire=0)
    {
		return setcookie(Cookie::$prefix.$key, $val, $expire, '/');
    }
	
    public static function unset_cookie($key)
    {
		return setcookie(Cookie::$prefix.$key, null, time()-3600, '/');
    }
	
    public static function get($key, $defaultValue = null)
    {
		if (isset($_COOKIE[Cookie::$prefix.$key])) return $_COOKIE[Cookie::$prefix.$key];
		else return $defaultValue;
    }
    
}
