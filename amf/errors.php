<?php
/**
 * Error codes
 **/

//OK
define(ERR_OK,10);
//Get errors 11..19
define(ERR_NOT_FOUND,11);
//Set errors 21..29
define(ERR_BAD_DATA,21);
//MySQL errors 31..39
define(ERR_MYSQL_SERVER_ERROR,31);
define(ERR_MYSQL_INSERT_FAIL,32);
define(ERR_MYSQL_DELETE_FAIL,33);
//Unknown command
define(ERR_UNKNOWN_COMMAND,40);

/**
 * Returns JSON encoded string for given error code
 * 
 * @param
 *        	<b>error_code</b> <i>int</i> Error code
 * @return <i>string</i> JSON-encoded string containing error code
 */
function errorCodeAsJSON($error_code) {
	return json_encode ( array (
			"error" => $error_code 
	) );
}
?>