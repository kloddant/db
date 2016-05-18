# db
A class that assists with using mysqli for prepared statements.

##Usage:

	require_once("db.php");

To make the initial connection, instantiate the class.  If you need to use multiple databases, you can either create another class instance, or you can change the connection for the current instance by using the connect method, which takes the same arguments as the class itself. 

	$db = new sql($host = "", $db_username = "", $db_password = "", $database = "")

For most queries, just use the query method.  For a SELECT statement, query returns a numeric array of associative arrays that can be accessed like $results[0]['thing'].  For anything else, query returns the statement object.  The query method saves the entire result set into memory as an associative array, so it is not appropriate for queries that return large result sets.

	$results = $db->query("
		SELECT *
		FROM stuff
		WHERE thing = ? AND money = ? AND index = ?;
	", array($dodad, $cost, $index), 'sdi');

The third parameter in this example is the types string.  s indicates a string, d indicates a double, i indicates an integer, and b would indicate a blob.  If this parameter is omitted, the execute method will decide what type they are.

To get the last inserted id, just call 

	$db->connection->insert_id;
	
To perform a query that will return a large result set, use prepare, execute, and fetch.  The execute method prepares the results array with the right keys and binds those keys, and the fetch method saves a row to those keys.

    $stmt = $db->prepare($sql);
	$executed_stmt = $this->execute($stmt, $parameters, $types);
	while ($executed_stmt->fetch()) {
		$results = $db->results;
	}
