<?php
class DbConnect {
	private $host = 'localhost';
	private $dbname = 'bkspace';
	private $username = 'root';
	private $password = '';
	private $conn;

	public function connect() {
		$this->conn = null;

		try {
			$this->conn = new mysqli($this->host, $this->username, $this->password, $this->dbname);
			
			if ($this->conn->connect_error) {
				throw new Exception("Connection failed: " . $this->conn->connect_error);
			}
			
			return $this->conn;
		} catch(Exception $e) {
			echo "Connection error: " . $e->getMessage();
			return null;
		}
	}
}
?>	