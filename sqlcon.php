<?php
class MySQLcon {
	private $mysqli;
	function __construct($server, $username, $password, $databse) {
		$this->mysqli = new mysqli ( $server, $username, $password, $databse );
		if ($this->mysqli->connect_errno) {
			throw new Exception ( "Error connecting to database: " . $this->$mysqli->connect_error );
		}
		$this->mysqli->query ( "SET CHARACTER SET utf8" );
	}
	function __destruct() {
		$this->mysqli->close ();
	}
	function escapeString($str) {
		return $this->mysqli->real_escape_string ( $str );
	}
	function goSQL($sql) {
		$res = $this->mysqli->query ( $sql );
		$err = $this->mysqli->connect_error;
		if (! $err)
			return $res;
		else
			throw new Exception ( "Error performing mySQL query:" . $err );
	}
	function getXMLResult($sql) {
		$res = $this->goSQL ( $sql );
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
	function getJSONResult($sql) {
		$res = $this->goSQL ( $sql );
		if ($res->num_rows == 1) 
			return json_encode( $res->fetch_assoc () );
		while ( $item = $res->fetch_assoc () ) {
			$items[] = $item;
		}
		return json_encode( $items );
	}	
}
?>