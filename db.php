<?php

function connect($host = "", $db_username = "", $db_password = "", $database = "") {
	$connection = mysqli_connect($host, $db_username, $db_password, $database);
	if (!$connection) {
	}
	$connection->set_charset("utf8");
	return $connection;
}



/*
Procedure: prepare
Purpose: To prepare sql queries.
Parameters:
	$sql: (string, required)  The sql string. 
Returns: A prepared statement object.
*/
function prepare($sql) {
	global $connection;
	// Prepare the statement.
	if (!($stmt = $connection->prepare($sql))) {
	}

	return $stmt;
}



/*
Procedure: convert_to_reference
Purpose: To convert values to variable references.
Parameters:
	$value (string, required)	An value to be converted to a reference. 
Returns: The input variable converted into a reference instead of a variable.
Note: Helper function for convert_to_references.
*/
function convert_to_reference(&$value) {
    return $value;
}



/*
Procedure: execute
Purpose: To execute prepared queries and return the results.
Parameters:
	$stmt: 			(object, required)  The prepared statement. 
	$parameters: 	(array, required)  	The array of parameters that is supposed to go into the sql string.
	$types: 		(string, optional)	The string of types that is supposed to accompany the parameters array, in the same order.
										Any types that are omitted will be treated as strings. 
										i = integer, d = double, s = string, b = blob.
Returns: An associative array of results for SELECT statements or the stmt object otherwise.
Preconditions: Ideally, the refValues function needs to be defined outside so that it doesn't need to be redefined each time this function runs.
*/
function execute($stmt, $parameters = array(), $types = '') {
	global $connection;
	if (count($parameters) > 0) {
		// Define a function that will allow us to pass the bind_param variables as references instead of as values.
		if (!function_exists('refValues')) {
			function refValues($arr){
			    $refs = array();
			    foreach($arr as $key => $value)
			        $refs[$key] = &$arr[$key];
			    return $refs;
			}
		}
		// Rectify any inconsistencies between $parameters and $types.
		$difference = count($parameters) - strlen($types);
		if ($difference > 0) {
			for ($i = 0; $i < $difference; $i++) {
				$types .= 's';
			}
		}
		else if ($difference < 0) {
			$types = substr($types, 0, $difference);
		}
		// Add the $types string to the beginning of the parameters array.
		array_unshift($parameters, $types);
		// Bind the variables to the prepared statement.
		call_user_func_array(array($stmt, "bind_param"), array_map("convert_to_reference", $parameters));
		unset($parameters);
	}
	// Execute the statement.
	if (!$stmt->execute()) {
	}
	// If the statement was a SELECT query, then return the results.
	else if ($stmt->affected_rows < 0) {
		$stmt->store_result();
		$fields = $stmt->result_metadata()->fetch_fields();
		$params = array();
	    foreach ($fields as $field) {
	        $key = str_replace(' ', '_', $field->name);
	        if (!in_array($key, $params)) {
        		$params[$key] = &$field->name;
        	} 
        	else {
        	}
	    }
	    call_user_func_array(array($stmt, 'bind_result'), $params);
    	$result = array();
	    while ($stmt->fetch()) {
	        $result[] = array_map("convert_to_reference", $params);
	    }
	}
	// Otherwise, if the statement was INSERT, DELETE, UPDATE, or something, then return the last inserted id.
	else {
		$result = $connection->insert_id;
	}

	$stmt->free_result();
	return $result;
}




/*
Procedure: query
Purpose: To perform prepared statement queries on the database.  This is a shortcut for the prepare and execute functions that is good for one-time uses.
Parameters:
	$sql: 		 	(string, required)  The sql string.
	$parameters: 	(array, required)  	The array of parameters that is supposed to go into the sql string.
	$types: 		(string, optional)	The string of types that is supposed to accompany the parameters array, in the same order.
										Any types that are omitted will be treated as strings. 
										i = integer, d = double, s = string, b = blob. 
Returns: An associative array of results for SELECT statements or the stmt object otherwise.
Preconditions: The prepare and execute functions must be defined.
*/
function query($sql, $parameters = array(), $types = '') {
	$stmt = prepare($sql);
	return execute($stmt, $parameters, $types);
}

?>
