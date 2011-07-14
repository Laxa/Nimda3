<?php

class MySQL {
	
	
	private $host;
	private $user;
	private $password;
	private $db;
	private $port;
	
	private $Instance=false;
	
	function __construct($host, $user, $password, $db, $port=3306) {
		$this->host     = $host;
		$this->user     = $user;
		$this->password = $password;
		$this->db       = $db;
		$this->port     = $port;
	}
	
	private function connect() {
		$this->Instance = new mysqli($this->host,$this->user,$this->password,$this->db);
		if($this->Instance->connect_error) die($this->Instance->connect_error);
		$this->query("set character set utf8");
	}
	
	public function query($sql, $mode='assoc') {
		if(!$this->Instance) {
			$this->connect();
		}
		
		if(false === $Result = $this->Instance->query($sql)) {
			if($this->Instance->error == 'MySQL server has gone away') {
				$this->connect();
				return $this->query($sql);
			} else {
				die("\nMySQL Error: ".$this->Instance->error."\n");
			}
		}
		
		preg_match('/^(\w+)/', strtoupper($sql), $arr);
		
		switch($arr[1]) {
			case 'SELECT': case 'SHOW':
				$return = array();
				
				switch($mode) {
					case 'assoc':
						while($row = $Result->fetch_assoc()) $return[] = $row;
					break;
					case 'numeric':
						while($row = $Result->fetch_row()) $return[] = $row;
					break;
					case 'both':
						while($row = $Result->fetch_array()) $return[] = $row;
					break;
				}
			break;
			case 'INSERT':
				$return = $this->Instance->insert_id;
			break;
			case 'UPDATE':
				$return = $this->Instance->affected_rows;
			break;
			default:
				$return = true;
			break;
		}
		
	return $return;
	}
	
	public function multiQuery($sql) {
		$this->Instance->multi_query($sql);
		do {
			if($this->Instance->error) die("\nMySQL Error: ".$this->Instance->error."\n");
			if(false !== $Result = $this->Instance->use_result()) $Result->close();
		} while($this->Instance->more_results() && $this->Instance->next_result());
		
	return true;
	}
	
	public function fetchColumn($sql) {
		$res = $this->query($sql);
		if(empty($res)) return false;
		
	return current($res[0]);
	}
	
	public function fetchRow($sql, $mode='assoc') {
		$res = $this->query($sql, $mode);
		if(empty($res)) return false;
		
	return $res[0];
	}
	
}

?>
