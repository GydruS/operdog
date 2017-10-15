<?php

class MemcacheAdapter implements CacheInterface
{
    protected $address = '127.0.0.1';
    protected $port = '11211';
    protected $user = '';
    protected $password = '';
    private $enabled = false;
    public $memcache = null;
    
    public function __construct($address = null, $port = null, $autoConnect = true) {
        if ($autoConnect) $this->connect($address, $port, null, null);
		else $this->init($address, $port, null, null, $autoConnect);
    }
    
	public function connect($address = null, $port = null, $user = null, $password = null) {
        $this->init($address, $port, $user, $password, false);
        if (extension_loaded('memcache')) {
            try {
                if (empty($this->memcache)) $this->memcache = new Memcache();
                if ($this->memcache->pconnect($this->address, $this->port))
					$this->enabled = true;
            } catch(RedisException $e) {
            }
        }
        return $this->isConnected();
    }
    
	public function init($address = null, $port = null, $user = null, $password = null, $autoConnect = false) {
        if ($address !== null) $this->address = $address;
        if ($port !== null) $this->port = $port;
        if ($user !== null) $this->user = $user;
        if ($password !== null) $this->password = $password;
		if ($autoConnect) $this->connect();
    }
    
    public function enabled() {
        return $this->enabled;
    }
    
    public function set($key, $val, $ttl = 0, $flags = 0) {
        if ($this->enabled()) return $this->memcache->set($key, $val, $flags, $ttl);
		else return false;
	}
    
    public function replace($key, $val, $ttl = 0, $flags = 0) {
        if ($this->enabled()) return $this->memcache->replace($key, $val, $flags, $ttl);
		else return false;
	}
    
    public function get($key, &$flags = null) {
        if ($this->enabled()) return $this->memcache->get($key, $flags);
		else return false;
	}
    
    public function del($key) {
        if ($this->enabled()) return $this->memcache->del($key);
		else return false;
	}
    
    public function flush() {
        if ($this->enabled()) return $this->memcache->flush();
		else return false;
	}
    
	public function isConnected() {
        return $this->enabled;
    }
}
