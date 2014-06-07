<?php
include 'sqlcon.php';

try {
	$sql = new MySQLcon ( '127.0.0.1', 'root', '', 'inventory' );
} catch ( Exception $e ) {
	die ( $e->getMessage () );
}

$act = $_GET ['act'];
(isSet ( $_GET ['qr'] )) && ($QR = $sql->escapeString ( $_GET ['qr'] ));
(isSet ( $_POST ['memo'] )) && ($MEMO = $sql->escapeString ( $_POST ['memo'] ));
(isSet ( $_POST ['name'] )) && ($NAME = $sql->escapeString ( $_POST ['name'] ));
((is_numeric ( $_GET ['state'] )) && ($STATE = $_GET ['state'])) || ($STATE = 0);

try {
	switch ($act) {
		case 'getAll' :
			$res = $sql->getXMLResult ( "SELECT * FROM data WHERE 1;" );
			break;
		case 'getProblems' :
			$res = $sql->getXMLResult ( "SELECT * FROM data WHERE state<>0;" );
			break;
		case 'getQR' :
			$res = $sql->getXMLResult ( "SELECT * FROM data WHERE qr='$QR' LIMIT 1;" );
			break;
		case 'set' :
			if ($QR == '') throw new Exception('QR must be set');
			if ($NAME == '') throw new Exception('Name must be set');
			$sql->goSQL ( "INSERT INTO data VALUES ('$QR','$NAME','$MEMO',$STATE) ON DUPLICATE KEY UPDATE qr=qr;" );
			break;
		default: throw new Exception('Unknown command');	
	}
	$res? (header('Content-type: application/xml') || (print $res)) : print 'ok';
	/*
	 * debug 
	 * echo "<form action=\"?act=set&qr=0123456789ABCDEF\" method=\"POST\"> QR:<br> <input name=\"qr\"><br> Name:<br> <input name=\"name\"><br> Memo:<br> <textarea name=\"memo\"></textarea><br> State:<br> <input name=\"state\"><br> <input type=\"submit\"> </form>";
	 */
} catch ( Exception $e ) {
	die ( $e->getMessage () );
}
?>