<?php

class RedisAdapter implements CacheInterface
{
    protected $address = '127.0.0.1';
    protected $port = 6379;
    protected $user = '';
    protected $password = '';
    private $enabled = false;
    public $redis = null;
    
    public function __construct($address = null, $port = null, $password = null, $autoConnect = true) {
        if ($autoConnect) $this->connect($address, $port, null, $password);
		else $this->init($address, $port, null, $password, $autoConnect);
    }
    
	public function connect($address = null, $port = null, $user = null, $password = null) {
        $this->init($address, $port, $user, $password);
        if (extension_loaded('redis')) {
            try {
                if (empty($this->redis)) $this->redis = new Redis();
                $this->enabled = $this->redis->pconnect($this->address);
                if ($this->enabled && !empty($this->password)) $this->redis->auth($this->password);
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
        if ($this->enabled()) {
			return $ttl ? $this->redis->setex($key, $ttl, $val): $this->redis->set($key, $val);
		}
		else return false;
	}
    
    public function get($key, &$flags = null) {
        if ($this->enabled()) return $this->redis->get($key);
		else return false;
	}
    
    public function del($key) {
        if ($this->enabled()) return $this->redis->del($key);
		else return false;
	}
    
	public function isConnected() {
        return $this->enabled;
    }
}
