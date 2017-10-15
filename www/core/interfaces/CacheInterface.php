<?php 

interface CacheInterface {
	public function connect($address, $port, $user = '', $password = '');
    public function enabled();
//    public function set($key, $val);
    public function set($key, $val, $ttl = 0, $flags = 0);
//    public function setex($key, $ttl, $val, $flags = null);
    public function get($key, &$flags = null);
    public function del($key);
	public function isConnected();
}
