<?php
include 'sqlcon.php';
include 'errors.php';
include 'states.php';

function returnCode($c) {
	return json_encode ( array ( "error"=>$c ) );
}

//main code

try {
	$sql = new MySQLcon ( '127.0.0.1', 'root', '', 'inventory' );
} catch ( Exception $e ) {
	die ( returnCode (ERR_MYSQL_SERVER_ERROR) );
}

$act = $_GET ['act'];
(isSet ( $_GET  ['show'] )) && ($show = $sql->escapeString ( $_GET  ['show'] ));
(isSet ( $_POST ['memo'] )) && ($memo = $sql->escapeString ( $_POST ['memo'] ));
(isSet ( $_POST ['name'] )) && ($name = $sql->escapeString ( $_POST ['name'] ));
(isSet ( $_REQUEST ['code'] )) && ($code = $sql->escapeString ( $_REQUEST ['code'] ));
(isSet ( $_REQUEST ['location'] )) && ($location = $sql->escapeString ( $_REQUEST ['location'] ));
((is_numeric ( $_POST ['state'] )) && ($state = $_POST ['state'])) || ($state = STATE_OK);

try {
	switch ($act) {
		case 'get' :
			switch ($show) {
				case 'all' :
					$res = $sql->getJSONResult ( "SELECT * FROM data WHERE location like '$location%' ORDER BY location,code;" );
					break;
				case 'problems' :
					$res = $sql->getJSONResult ( "SELECT * FROM data WHERE (location like '$location%') AND (state<>".STATE_OK.") ORDER BY location,code;" );
					break;
				default:
					$res = $sql->getJSONResult ( "SELECT * FROM data WHERE code='$show' LIMIT 1;" );
			}
			if ( !$res ) throw new Exception(ERR_NOT_FOUND);
			break;
		case 'set' :
			if (($code == '') || ($name == '')) throw new Exception(ERR_BAD_DATA);
			$sql->goSQL ( "INSERT INTO data VALUES ('$code','$name','$memo','$location',$state) ON DUPLICATE KEY UPDATE name='$name',memo='$memo',location='$location',state=$state;" );			
			die (returnCode (ERR_OK));
			break;
		case 'del' :
			if ($code == '') throw new Exception(ERR_BAD_DATA);
			$sql->goSQL ( "DELETE FROM data WHERE code='$code' LIMIT 1;" );
			die (returnCode (ERR_OK));
			break;
		case 'getStates' :
			die ( json_encode ($STATES_STR) );
			break;			
		default: throw new Exception(ERR_UNKNOWN_COMMAND);	
	}
	echo $res;
} catch ( Exception $e ) {
	die ( returnCode ($e->getMessage ()) );
}
?>
