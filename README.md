# db
A set of functions that assists with using mysqli for prepared statements.

##Usage:

	require_once("db.php");

To make the initial connection, use connect.  Connect stores the connection in the $GLOBALS['connection'] variable and returns it.  

	connect($host = "", $db_username = "", $db_password = "", $database = "")

For a select, update, or delete statement, just use query.  If no connection is specified, query will use the one that is in the $GLOBALS['connection'] variable.  For a select statement, query returns a numeric array of associative arrays that can be accessed like $results[0]['thing'].

	$results = query("
		SELECT *
		FROM stuff
		WHERE thing = ? AND money = ? AND index = ?;
	", array($dodad, $cost, $index), 'sdi');
	
For an insert statement, also just use query.  It will return the last inserted id.

	$last_inserted_id = query("
		INSERT INTO stuff (thing, money, index)
		VALUES (?, ?, ?);
	", array($dodad, $cost, $index), 'sdi');

The third parameter in both of these examples is the types string.  s indicates a string, d indicates a double, and i indicates an integer.  If this parameter is omitted, all variables that are used in the sql statements will be treated as strings.
