<?php
include 'sqlcon.php';
include 'errors.php';

try {
	$sql = new MySQLcon ( '127.0.0.1', 'root', '', 'inventory' );
} catch ( Exception $e ) {
	die ( json_encode ( array ( "error"=>ERR_MYSQL_SERVER_ERROR ) ) );
}

$act = $_GET ['act'];
(isSet ( $_GET ['qr'] )) && ($QR = $sql->escapeString ( $_GET ['qr'] ));
(isSet ( $_POST ['memo'] )) && ($MEMO = $sql->escapeString ( $_POST ['memo'] ));
(isSet ( $_POST ['name'] )) && ($NAME = $sql->escapeString ( $_POST ['name'] ));
((is_numeric ( $_POST ['state'] )) && ($STATE = $_POST ['state'])) || ($STATE = 0);

try {
	switch ($act) {
		case 'getAll' :
			$res = $sql->getJSONResult ( "SELECT * FROM data WHERE 1;" );
			break;
		case 'getProblems' :
			$res = $sql->getJSONResult ( "SELECT * FROM data WHERE state<>0;" );
			break;
		case 'getQR' :
			$res = $sql->getJSONResult ( "SELECT * FROM data WHERE qr='$QR' LIMIT 1;" );
			break;
		case 'set' :
			if (($QR   == '') || ($NAME == '')) throw new Exception(ERR_BAD_DATA);
			$sql->goSQL ( "INSERT INTO data VALUES ('$QR','$NAME','$MEMO',$STATE) ON DUPLICATE KEY UPDATE qr=qr;" );
			throw new Exception(ERR_OK);
			break;
		default: throw new Exception(ERR_UNKNOWN_COMMAND);	
	}
	if (empty($res)) throw new Exception(ERR_NOT_FOUND);
	echo $res;
} catch ( Exception $e ) {
	die ( json_encode ( array ( "error"=>$e->getMessage() ) ) );
}
?>
