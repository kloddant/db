# db
A set of functions that assists with using mysqli for prepared statements.

##Usage:

	require_once("db.php");

To make the initial connection, use connect.  Connect stores the connection in the $GLOBALS['connection'] variable and returns it.  

	connect($host = "", $db_username = "", $db_password = "", $database = "")

For any statement, just use query.  If no connection is specified, query will use the one that is in the $GLOBALS['connection'] variable.  For a SELECT statement, query returns a numeric array of associative arrays that can be accessed like $results[0]['thing'].  For anything else, query returns the statement object.

	$results = query("
		SELECT *
		FROM stuff
		WHERE thing = ? AND money = ? AND index = ?;
	", array($dodad, $cost, $index), 'sdi');

The third parameter in this example is the types string.  s indicates a string, d indicates a double, i indicates an integer, and b would indicate a blob.  If this parameter is omitted, all variables that are used in the sql statements will be treated as strings.

To get the last inserted id, just call 

	$connection->insert_id;
