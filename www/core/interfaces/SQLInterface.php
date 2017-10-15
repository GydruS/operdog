<?php 

interface SQLInterface {
	public function connect($host, $user, $password);
	public function query($sql);
    public function multiQuery($sql);
	public function close();
	public function fetchArray($result, $resultType);
    public function escapeString($str);
	public function isConnected();
}
