# db
A php include that defines and instantiates a sql class that has a set of methods that assist with using mysqli for prepared statements.  The sql class is basically a wrapper for the mysqli::stmt and mysqli:result classes rolled into one.

##Including
    require_once("db.php");

##Connection
To make the initial connection, instantiate the class.

    $db = new sql($host = "", $db_username = "", $db_password = "", $database = "");

##Queries
For pretty much anything, use the query method.  The query method has a buffer parameter, which is set to default to true.  For simplicity, you can set $buffer = true when you expect small result sets, but to save memory for large result sets, set $buffer = false.  
###Buffered Queries
With $buffer = true, for a SELECT statement, query returns a numeric array of associative arrays that can be accessed like $results[0]['thing'].

    $results = $db->query("
        SELECT *
        FROM stuff
        WHERE thing = ? AND money = ? AND index = ?;
    ", array($dodad, $cost, $index), 'sdi');

The third parameter in this example is the types string. s indicates a string, d indicates a double, i indicates an integer, and b would indicate a blob. If this parameter is omitted, the execute method will determine what type the parameters are.

###Un-buffered Queries
Because the query method saves the result set to memory, it should not be used where large result sets are expected, so set $buffer to false.

    $results = $db->query("
        SELECT *
        FROM stuff
        WHERE thing = ? AND money = ? AND index = ?;
    ", array($dodad, $cost, $index), 'sdi', false);
    while ($stuff = $results->fetch_assoc()) {
        var_dump($stuff);
    }

To get the last inserted id, just call

    $connection->insert_id;
