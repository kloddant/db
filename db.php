<?php

class sql {

	public $connection;
	public $row;

	public function __construct($host = NULL, $username = NULL, $password = NULL, $database = NULL) {
		$this->connect($host, $username, $password, $database);
	}

	public function connect($host = "localhost", $username = "root", $password = NULL, $database = NULL) {

		if (!isset($password)) {
			exit('Error: No database password defined.');
		}
		if (!isset($database)) {
			exit('Error: No database defined.');
		}

		$connection = new mysqli($host, $username, $password, $database);
		if (!$connection) {
			exit("Error: Could not connect to database.");
		}

		$connection->set_charset("utf8");

		$this->connection = $connection;
		return $connection;
	}

	public function prepare($sql, $connection = NULL) {

		if (!isset($connection)) {
			$connection = $this->connection;
		}
		if (!($stmt = $connection->prepare($sql))) {
		    echo "Prepare failed: (" . $connection->errno . ") " . $connection->error;
		}
		return $stmt;

	}

	private static function convert_to_reference(&$value) {
		return $value;
	}

	public function execute($stmt, $parameters = array(), $types = '') {

		if (count($parameters) > 0) {
			// Rectify any inconsistencies between $parameters and $types.
			$number_of_parameters = count($parameters);
			$number_of_types = strlen($types);
			$difference = $number_of_parameters - $number_of_types;
			if ($difference > 0) {
				for ($i = $number_of_types; $i < $number_of_parameters; $i++) {
					if (is_int($parameters[$i])) {
						$types .= 'i';
					}
					else if (is_float($parameters[$i])) {
						$types .= 'd';
					}
					else {
						$types .= 's';
					}
				}
			}
			else if ($difference < 0) {
				$types = substr($types, 0, $difference);
			}
			array_unshift($parameters, $types);
			call_user_func_array(array($stmt, "bind_param"), array_map("sql::convert_to_reference", $parameters));
		}

		// Execute the statement.
		if (!$stmt->execute()) {
		    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		}
		// If the statement was a SELECT query, then create the empty bound parameters array.
		else if ($stmt->affected_rows < 0) {
			$stmt->store_result();
			$fields = $stmt->result_metadata()->fetch_fields();
			$params = array();
			$duplicates = array();
		    foreach ($fields as $field) {
		        $key = str_replace(' ', '_', $field->name);
		        if (!array_key_exists($key, $duplicates)) {
		        	$duplicates[$key] = 0;
		    	}
		        if (!array_key_exists($key, $params)) {
	        		$params[$key] = &$field->name;
	        	} 
	        	else {
	        		$duplicates[$key] += 1;
	        		$params["duplicate_".$key."_".$duplicates[$key]] = &$field->name;
	        	}
		    }
		    call_user_func_array(array($stmt, 'bind_result'), $params);
		    $this->row = $params;
		}
		return $stmt;

	}

	public function query($sql, $parameters = array(), $types = '', $connection = NULL) {
	
		if (!isset($connection)) {
			$connection = $this->connection;
		}
		$stmt = $this->prepare($sql, $connection);
		$executed_stmt = $this->execute($stmt, $parameters, $types);
		$i = 0;
		$results = array();
		while ($executed_stmt->fetch()) {
			$results[$i] = $this->row;
			$i += 1;
		}
		return $results;
	}

}

?>
