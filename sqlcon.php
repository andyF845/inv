<?php
class MySQLConnector {
	private $mysqli;
	public $errorInfo;
	
	/**
	 * Establish connection to mySQL server
	 *
	 * @param
	 *        	<b>server</b> <i>string</i> mySQL server name
	 * @param
	 *        	<b>username</b> <i>string</i> mySQL user name
	 * @param
	 *        	<b>password</b> <i>string</i> mySQL user password
	 * @param
	 *        	<b>database</b> <i>string</i> mySQL data base name
	 * @return bool Returns true on success or false on failure.
	 *         Additional error info is stored in errorInfo field;
	 *        
	 */
	function __construct($server, $username, $password, $databse) {
		$this->mysqli = new mysqli ( $server, $username, $password, $databse );
		$this->errorInfo = $this->mysqli->connect_error;
		if ($this->mysqli->connect_errno) {
			return false;
		}
		$this->mysqli->query ( "SET CHARACTER SET utf8" );
		return true;
	}
	function __destruct() {
		$this->mysqli->close ();
	}
	
	/**
	 * Returns sql-safe string for given string
	 *
	 * @param
	 *        	<b>str</b> <i>string</i> sql-string to be escaped
	 * @return <i>string</i> sql-escaped string.
	 */
	function escapeString($str) {
		return $this->mysqli->real_escape_string ( $str );
	}
	
	/**
	 * Performs a query on the database
	 *
	 * @param
	 *        	<b>sql</b> <i>string</i> query string
	 * @return false on failure.
	 *         For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries mysqli_query will return a mysqli_result object.
	 *         For other successful queries mysqli_query will return true.
	 */
	function goSQL($sql) {
		$res = $this->mysqli->query ( $sql );
		$err = $this->mysqli->connect_error;
		$this->errorInfo = $this->mysqli->error;
		if (! $err)
			return $res;
		else
			return false;
	}
	
	/**
	 * Performs a query on the database and returns result as associated array.
	 * <b>To perform non-select queries, please, use goSQL() instead.</b>
	 *
	 * @param
	 *        	<b>sql</b> <i>string</i> query string
	 * @return false on failure.
	 *         For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries will return an Array().
	 *         If the query returned empty data set, false will be returned.
	 */
	function getArrayResult($sql) {
		if ((! $res = $this->goSQL ( $sql )) || ($res->num_rows == 0))
			return false;
		while ( $item = $res->fetch_assoc () ) {
			$items [] = $item;
		}
		return $items;
	}
	
	/**
	 * Performs a query on the database and returns result as XML data string.
	 * <b>To perform non-select queries, please, use goSQL() instead.</b>
	 *
	 * @param
	 *        	<b>sql</b> <i>string</i> query string
	 * @return false on failure.
	 *         For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries will return an XML data string.
	 *         If the query returned empty data set, false will be returned.
	 */
	function getXMLResult($sql) {
		if ((! $res = $this->goSQL ( $sql )) || ($res->num_rows == 0))
			return false;
		$xml = new XMLWriter ();
		$xml->openMemory ();
		$xml->startDocument ( "1.0", "UTF-8" );
		$xml->startElement ( "items" );
		while ( $item = $res->fetch_assoc () ) {
			$xml->startElement ( "item" );
			foreach ( $item as $key => $value ) {
				$xml->writeElement ( $key, $value );
			}
			$xml->endElement ();
		}
		$xml->endElement ();
		return $xml->outputMemory ();
	}
	
	/**
	 * Performs a query on the database and returns result as JSON string.
	 * <b>To perform non-select queries, please, use goSQL() instead.</b>
	 *
	 * @param
	 *        	<b>sql</b> <i>string</i> query string
	 * @param
	 *        	<b>$defaultReturnValue</b> [optional]
	 *        	This value will be returned if function fails or query returns empty data set.
	 *        	Default value is false.
	 * @param
	 *        	<b>$returnAsArray</b> [optional]
	 *        	If true, JSON array is returned for single record.
	 *        	If false, JSON array is returned only for multiple records, single item for single record.
	 *        	Default value is false.
	 * @return defaultReturnValue on failure.
	 *         For successful SELECT, SHOW, DESCRIBE or EXPLAIN queries will return an Array().
	 *         If the query returned empty data set, defaultReturnValue will be returned.
	 */
	function getJSONResult($sql, $defaultReturnValue = false, $returnAsArray = false) {
		if (! $res = $this->goSQL ( $sql ))
			return $defaultReturnValue;
		switch ($res->num_rows) {
			case 0 :
				return $defaultReturnValue;
			case 1 :
				$res = $res->fetch_assoc ();
				if ($returnAsArray)
					$res = array ( $res );
				break;
			default :
				while ( $item = $res->fetch_assoc () ) {
					$items [] = $item;
				}
				$res = $items;
		}
		return json_encode ( $res );
	}
}
?>