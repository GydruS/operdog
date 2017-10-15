<?php
#################################
#       GydruS's Engine 3       #
#         "MySQL" class         #
#             v. 1.0            #
#    2012 10 09 - 2012 10 10    #
#################################

#################################
# Description
#--------------------------------
#
#

class MySQLiAdapter implements SQLInterface
{
    private $connection;
 
    public function connect($host, $user, $password) {
		$this->connection = mysqli_connect($host, $user, $password);
		/*if (!$this->connection) {
			die('Connect Error ('.mysqli_connect_errno().') '.mysqli_connect_error());
		}*/
		return $this->connection;
    }
	
    public function query($sql) {
		return mysqli_query($this->connection, $sql);
    }
	
    public function multiQuery($sql) {
		return mysqli_multi_query($this->connection, $sql);
    }
	
    public function close() {
		mysqli_close($this->connection);
    }

	public function fetchArray($result, $resultType = MYSQLI_ASSOC) {
        if (is_bool($result)) return false;
		return mysqli_fetch_array($result, $resultType);
	}
	
    public function escapeString($str) {
		return mysqli_escape_string($this->connection, $str);
    }
	
	public function isConnected() {
		if (!$this->connection)	return FALSE;
		else return TRUE;
	}	
	
	public function lastInsertId() {
		return mysqli_insert_id($this->connection);
	}	

	public function selectDB($database) {
		return mysqli_select_db($this->connection, $database);
	}

	public function getLastError() {
		return mysqli_error($this->connection);
	}

	public function getLastErrorCode() {
		return mysqli_errno($this->connection);
	}

}
