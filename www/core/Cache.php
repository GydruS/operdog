<?php

class Cache
{
    private $cacheAdapter;
	public $keysPrefix = '';
	public $errorCallback = null;
 
    public function __construct($engine, $errorCallback = null) {
        $this->errorCallback = $errorCallback;
		switch ($engine) {
			case 'redis' : Load::dbAdapter('RedisAdapter'); $this->cacheAdapter = new RedisAdapter(); break;
			case 'predis' : Load::dbAdapter('PRedisAdapter'); $this->cacheAdapter = new PRedisAdapter(); break;
			case 'memcache' : Load::dbAdapter('MemcacheAdapter'); $this->cacheAdapter = new MemcacheAdapter(); break;
 		}
	}
	
    public function connect($address, $user, $password) {
		return $this->cacheAdapter->connect($address, $user, $password);
    }
	
    public function set($key, $val) {
        return $this->cacheAdapter->set($key, $val);
	}
    
    public function setex($key, $ttl, $val) {
        return $this->cacheAdapter->setex($key, $ttl, $val);
	}
    
    public function get($key) {
        return $this->cacheAdapter->get($key);
	}
    
    public function del($key) {
        return $this->cacheAdapter->del($key);
	}
    
	public function isConnected() {
        return $this->cacheAdapter->isConnected();
    }

}
