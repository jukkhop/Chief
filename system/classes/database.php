<?php

class Database {
	
	private $connection = false;
	
	public function __construct($host = null, $user = null, $pass = null, $db = null) {		
		if(!is_null($host) && !is_null($user) && !is_null($pass) && !is_null($db)) {
			return $this->connect($host, $user, $pass, $db);
		}
	}
	
	# Connect to database
	public function connect($host, $user, $pass, $db, $driver = 'mysql') {
		if(empty($user)) return false;
		try {
			switch($driver) {
				case 'mysql':
				case 'mariadb':
					$this->connection = new PDO(
						sprintf('mysql:host=%s;dbname=%s', $host, $db),
						$user,
						$pass,
						[PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
					);
					break;
				case 'pgsql':
					$this->connection = new PDO(
						sprintf('pgsql:dbname=%s;host=%s;', $host, $db),
						$user,
						$pass);
					break;
				case 'sqlsrv':
					$this->connection = new PDO(
						sprintf('sqlsrv:Server=%s;Database=%s;', $host, $db),
						$user,
						$pass);
					break;
				case 'sqlite':
					$this->connection = new PDO(
						sprintf('sqlite:%s', $db),
						$user,
						$pass);
					break;
			}
			return isset($this->connection) ? $this->connection : false;
		} catch(Exception $e) {
			return false;
		}
	}
	
	# Internal function for executing queries 
	private function execute(Array $args) {
		if(!$this->connection) {
			throw new Exception('No database connection established.');
		}
		
		if(empty($args)) {
			return false;
		}
		
		$sql = array_shift($args);
		$statement = $this->connection->prepare($sql);
		
		try {
			$statement->execute($args);
		} catch (PDOException $e) {
			echo $e->getMessage()."\n";
			$trace = $e->getTrace();
			if(isset($trace[1])) {
				echo 'Called in '.$trace[1]['file'].' on line '.$trace[1]['line']."\n";
			}
		}
		return $statement;
	}

	# Run a query
	public function query() {
		return $this->execute(func_get_args());
	}
	
	# Return all rows
	public function all() {
		$statement = $this->execute(func_get_args());
		return $statement->fetchAll();
	}
	
	# Return a single column
	public function column() {
		$statement = $this->execute(func_get_args());
		$result = array();
		foreach($statement->fetchAll() as $row) {
			$row = (array)$row;
			$result[] = array_shift($row);
		}
		return $result;
	}
	
	# Return a single row
	public function row() {
		$statement = $this->execute(func_get_args());
		return $statement->fetchObject();
	}
	
	# Return the first value in the first row
	public function one($resource = null) {
		$statement = $this->execute(func_get_args());
		return $statement->fetchColumn();
	}
	
	# Return the count of found rows
	public function count($resource = null) {
		$statement = $this->execute(func_get_args());
		return $statement->rowCount();		
	}
	
	# Escape variables
	public function escape($str) {
		return is_scalar($str) ? PDO::quote($str) : $str;
	}
	
	# Insert
	public function insert($table, $fields) {
		$query = sprintf("INSERT INTO `%s` SET\n", $this->escape($table));
		$values = array();
		foreach($fields as $key => $value) {
			if(is_null($value)) {
				$value = 'NULL';
			} else {
				$value = sprintf("'%s'", $this->escape($value));
			}
			$values[] = sprintf("`%s` = %s", $this->escape($key), $value);
		}
		$query .= implode(",\n", $values);
		$statement = $this->query($query);
		return $statement === false ? false : PDO::lastInsertId;
	}
	
	# Replace
	public function replace($table, $fields) {
		$query = sprintf("REPLACE INTO `%s` SET\n", $this->escape($table));
		$values = array();
		foreach($fields as $key => $value) {
			if(is_null($value)) {
				$value = 'NULL';
			} else {
				$value = sprintf("'%s'", $this->escape($value));
			}
			$values[] = sprintf("`%s` = %s", $this->escape($key), $value);
		}
		$query .= implode(",\n", $values);
		$statement = $this->query($query);
		return $statement === false ? false : PDO::lastInsertId;
	}
	
	# Update
	public function update($table, $fields, $where = null) {
		$query = sprintf("UPDATE `%s` SET\n", $this->escape($table));
		$values = array();
		foreach($fields as $key => $value) {
			if(is_null($value)) {
				$value = 'NULL';
			} else {
				$value = sprintf("'%s'", $this->escape($value));
			}
			$values[] = sprintf("`%s` = %s", $this->escape($key), $value);
		}
		$query .= implode(",\n", $values)."\n";
		
		if(is_array($where)) {
			$_where = array();
			foreach($where as $key => $value) {
				$_where[] = sprintf("`%s` = '%s'", $this->escape($key), $this->escape($value));
			}
			$where = implode(" AND ", $_where);
		} else {
			$where = sprintf("`id` = '%s'", $this->escape($where));
		}
		$query .= 'WHERE '.$where;
		
		$statement = $this->query($query);
		return $result === false ? false : $statement->rowCount();
	}
	
	# Begin transaction
	public function begin() {
		$this->connection->beginTransaction();
	}
	
	# Commit transaction
	public function commit() {
		$this->connection->commit();
	}
	
	# Rollback transaction
	public function rollback() {
		$this->connection->rollBack();
	}
}
