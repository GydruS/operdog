<?php
#################################
#       GydruS's Engine 3       #
#     "SQLiteAdapter" class     #
#             v. 1.0            #
#    2015 03 29 - 2015 03 29    #
#################################

#################################
# Description
#--------------------------------
#
#

class SQLiteAdapter implements SQLInterface
{
    private $connection;
    private $filename;
 
    public function open($filename, $flags = null, $encryption_key = null) {
        $this->filename = $filename;
        $this->connection = new SQLite3($filename, $flags, $encryption_key);
		/*if (!$this->connection) {
			die('Connect Error ('.mysqli_connect_errno().') '.mysqli_connect_error());
		}*/
        return $this->connection;
    }
    
    public function connect($host, $user, $password) {
        return $this->open($host, $user, $password);
    }
	
    public function query($sql) {
        return $this->connection->query($sql);
    }
	
    public function multiQuery($sql) {
        return $this->connection->query($sql);
    }
	
    public function close() {
        return $this->connection->close();
    }

	public function fetchArray($result, $resultType = MYSQLI_ASSOC) {
        return false;
        //if (is_bool($result)) return false;
		//return mysqli_fetch_array($result, $resultType);
	}
	
    public function escapeString($str) {
		return $this->connection->escapeString($str);
    }
	
	public function isConnected() {
		if (!$this->connection)	return FALSE;
		else return TRUE;
	}	
	
	public function lastInsertId() {
		return $this->connection->lastInsertRowID();
	}	

	public function selectDB($database) {
		return false;
	}

	public function getLastError() {
		return $this->connection->lastErrorMsg();
	}

	public function getLastErrorCode() {
		return $this->connection->lastErrorCode();
	}

}
