<?php

class sql {

	private $connection;

	public function __construct($host = NULL, $username = NULL, $password = NULL, $database = NULL) {
		$this->connect($host, $username, $password, $database);
	}

	public function __destruct() {
		if ($this->connection) {
			$this->connection->close();
		}
	}

	protected static function convert_to_reference(&$value) {
		return $value;
	}

	public static function question_marks(array $array) {
		return implode(",", array_fill(0, count($array), "?"));
	}

	public static function transpose(array $array) {
		$transposed = array();
		foreach ($array as $row => $values) {
			foreach ($values as $column => $cell) {
				$transposed[$column][$row] = $cell;
			}
		}
		return $transposed;
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
			exit("We're sorry, but we can't connect to the database at the moment.  Please try again later.");
		}

		$connection->set_charset("utf8");
		$this->connection = $connection;
		return $connection;
	}

	public function prepare($sql) {
		$connection = $this->connection;
		if (!($stmt = $connection->prepare($sql))) {
		    echo "Prepare failed: (" . $connection->errno . ") " . $connection->error;
		    $stmt = false;
		}
		else {
			$stmt = new sql_stmt($stmt);
		}
		return $stmt;
	}

	public function query($sql, array $parameters = array(), $types = '') {
		$stmt = $this->prepare($sql);
		$executed_stmt = $stmt->execute($parameters, $types);
		$results = false;
		if ($executed_stmt) {
			$results = $executed_stmt->fetch_all("MYSQLI_ASSOC");
			$results = ($results ? $results : $this->insert_id());
		}
		return $results;
	}

	public function insert_id() {
		return $this->connection->insert_id;
	}

}

class sql_stmt extends sql {

	private $stmt;

	public function __construct($stmt) {
		$this->stmt = $stmt;
	}

	public function execute(array $parameters = array(), $types = '') {
		$stmt = $this->stmt;

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
		    exit("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
		}
		// If the statement was a SELECT query, then create the empty bound parameters array.
		$row = "";
		if ($stmt->affected_rows < 0) {
			$stmt->store_result();
			$fields = $stmt->result_metadata()->fetch_fields();
			$params = array();
			$duplicates = array();
		    foreach ($fields as &$field) {
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
		    $row = $params;
		}

		$sql_result = new sql_result($stmt, $row);
		return $sql_result;

	}

}

class sql_result extends sql {

	private $row = array();
	private $stmt;

	public function __construct($stmt, $row) {
		$this->stmt = $stmt;
		$this->row = $row;
	}

	public function fetch_row() {
		return ($this->stmt->fetch() ? array_values(array_map("sql_result::convert_to_reference", $this->row)) : false);
	}

	public function fetch_assoc() {
		return ($this->stmt->fetch() ? array_map("sql_result::convert_to_reference", $this->row) : false);
	}

	public function fetch_array($resulttype = "MYSQLI_BOTH") {
		if ($resulttype == "MYSQLI_BOTH") {
			return ($this->stmt->fetch() ? array_merge($this->row, array_values($this->row)) : false);
		}
		else if ($resulttype == "MYSQLI_ASSOC") {
			return $this->fetch_assoc();
		}
		else if ($resulttype == "MYSQLI_NUM") {
			return $this->fetch_row();
		}
	}

	public function affected_rows() {
		return $this->stmt->affected_rows;
	}

	public function data_seek($offset = 0) {
		return $this->stmt->data_seek($offset);
	}

	public function field_count() {
		return $this->stmt->field_count;
	}

	public function num_rows() {
		return $this->stmt->num_rows;
	}

	public function fetch_all($resulttype = "MYSQLI_NUM") {
		$results = array();
		if ($resulttype == "MYSQLI_BOTH") {
			while ($result = $this->fetch_array("MYSQLI_BOTH")) {
				$results[] = $result;
			}
		}
		else if ($resulttype == "MYSQLI_ASSOC") {
			while ($result = $this->fetch_assoc()) {
				$results[] = $result;
			}
		}
		else if ($resulttype == "MYSQLI_NUM") {
			while ($result = $this->fetch_row()) {
				$results[] = $result;
			}
		}
		return $results;
	}

	public function free_result() {
		$this->stmt->free_result();
	}

}

?>
