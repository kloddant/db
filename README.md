# db
A php include that defines and instantiates classes and that assist with using mysqli for prepared statements. The classes are basically wrappers for the mysqli, mysqli_stmt, and mysqli_result classes. The main differences are that the wrapper functions contain fewer properties and methods, and the sql_stmt::execute method has two new arguments. 

## Compatibility
The function definition for convert_to_reference needs to be written differently for php 5.6 vs php 5.7.

### php 5.6
    protected static function convert_to_reference($value) {
    
### php 5.7
    protected static function &convert_to_reference($value) {

## Including
    require_once("db.php");

## Connection
To make the initial connection, instantiate the class.

    $db = new sql($host = "", $db_username = "", $db_password = "", $database = "");
    
To change connections, use the connect method.

    $db->connect($host = "", $db_username = "", $db_password = "", $database = "");

## Queries
### Single, Small Queries
For queries that you only run once and that you expect to return a small result set, use the query method. For a SELECT statement, query returns a numeric array of associative arrays that can be accessed like $results[0]['thing'].

    $results = $db->query("
        SELECT *
        FROM stuff
        WHERE thing = ? AND money = ? AND index = ?;
    ", array($dodad, $cost, $index), 'sdi');
    var_dump($results);

The third parameter in this example is the types string. s indicates a string, d indicates a double, i indicates an integer, and b would indicate a blob. If this parameter is omitted, the execute method will determine what type the parameters are.

### Repeated, Large Queries
For queries that you expect to run repeatedly or whose result sets are large enough that you don't want to store them in memory all at once, run the query the longer way. 

     $stmt= $db->prepare("
        SELECT *
        FROM stuff
        WHERE thing = ? AND money = ? AND index = ?;
    ");
    $results = $stmt->execute(array($dodad, $cost, $index), 'sdi');
    while ($row = $results->fetch_assoc()) {
        var_dump($row);
    }

### Last Inserted Id
To get the last inserted id, just call

    $db->insert_id();
    
### IN ###
For queries that use IN, use sprintf in conjunction with implode, array_fill, and count to create and insert a string with a dynamic number of question marks before you prepare the query.

    $progenitor = $db->query(sprintf("
		SELECT *
		FROM posts
		WHERE ID IN (%s)
    ", implode(",", array_fill(0, count($ancestor_ids), "?"))), $ancestor_ids);

