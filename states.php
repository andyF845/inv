<?php
//States for client app
define(STATE_OK,10);
define(STATE_BROKEN_HARD,11);
define(STATE_BROKEN_SOFT,21);
define(STATE_MAINTENANCE,31);
define(STATE_PARTS,41);
define(STATE_DISPOSE,51);

$STATES_STR = array( 	
	STATE_OK => 'Работает',
	STATE_BROKEN_HARD => 'Сломан (ПО)',
	STATE_BROKEN_SOFT => 'Сломан (Железо)',
	STATE_MAINTENANCE => 'Обслуживание',
    STATE_PARTS => 'Запчасть',
    STATE_DISPOSE => 'Выкинуть');

function statesAsJSON () {
	global $STATES_STR;
	foreach ($STATES_STR as $key => $value) {
		$arr[] = array("key" => $key, "message" => $value);
	}
	return json_encode ($arr);
}
?>