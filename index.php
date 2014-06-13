<?php
/***MAIN SERVER CODE***/
/**
 * MAIN SERVER CODE
 * How does it work:
 * 1. script is called by client application
 * 2. establishing connection with mySQLserver usint MySQLCon class - sqlcon.php
 * 3. initializing script params (GET and POST data ) - mulvar.php
 * 4. performing asked action
 * 5. returning client result or error code in JSON
 */
include 'sqlcon.php';
include 'errors.php';
include 'states.php';
include 'mulvar.php';

// 2. connect to mySQL server
(! $sql = new MySQLconnector ( '127.0.0.1', 'root', '', 'inventory' )) || (errorCodeAsJSON ( ERR_MYSQL_SERVER_ERROR ));

// 3. init params
VarInit::$raw_vars = array ( 'act' ) ;
VarInit::$str_vars = array ( 'show', 'memo', 'name', 'code', 'location' );
VarInit::$int_vars = array ( 'state' );
VarInit::init ( $sql );

// 4,5. pars params
try {
	switch ($act) {
		case 'get' :
			switch ($show) {
				// get all
				case 'all' :
					$res = $sql->getJSONResult ( "SELECT * FROM data WHERE location like '$location%' ORDER BY location,code;", '[]' );
					break;
				// get problems
				case 'problems' :
					$res = $sql->getJSONResult ( "SELECT * FROM data WHERE (location like '$location%') AND (state<>" . STATE_OK . ") ORDER BY location,code;", '[]' );
					break;
				// code=show as default
				default :
					$res = $sql->getJSONResult ( "SELECT * FROM data WHERE code='$show' LIMIT 1;" );
			}
			if (! $res) // get returned nothing
				throw new Exception ( ERR_NOT_FOUND );
			else
				die ( $res );
			break;
		// set
		case 'set' :
			if (($code == '') || ($name == ''))
				throw new Exception ( ERR_BAD_DATA );
			$sql->goSQL ( "INSERT INTO data VALUES ('$code','$name','$memo','$location',$state) ON DUPLICATE KEY UPDATE name='$name',memo='$memo',location='$location',state=$state;" );
			die ( errorCodeAsJSON ( ERR_OK ) );
			break;
		// delete
		case 'del' :
			if ($code == '')
				throw new Exception ( ERR_BAD_DATA );
			$sql->goSQL ( "DELETE FROM data WHERE code='$code' LIMIT 1;" );
			die ( errorCodeAsJSON ( ERR_OK ) );
			break;
		// getStates
		case 'getStates' :
			die ( statesAsJSON () );
			break;
		// unknown command
		default :
			throw new Exception ( ERR_UNKNOWN_COMMAND );
	}
} catch ( Exception $e ) {
	die ( errorCodeAsJSON ( $e->getMessage () ) );
}
?>
