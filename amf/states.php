<?php
/**
 * States for client app
 */
define ( STATE_OK, 10 );
define ( STATE_BROKEN_HARD, 11 );
define ( STATE_BROKEN_SOFT, 21 );
define ( STATE_MAINTENANCE, 31 );
define ( STATE_PARTS, 41 );
define ( STATE_DISPOSE, 51 );
define ( STATE_DELETED, 61 );

$STATES_STR = array (
		STATE_OK => 'Работает',
		STATE_BROKEN_HARD => 'Сломан (ПО)',
		STATE_BROKEN_SOFT => 'Сломан (Железо)',
		STATE_MAINTENANCE => 'Обслуживание',
		STATE_PARTS => 'Запчасть',
		STATE_DISPOSE => 'Выкинуть', 
		STATE_DELETED => 'Удалить',
);

/**
 * Returns JSON encoded string for states
 *
 * @return <i>string</i> JSON-encoded string containing states int values and messages
 */
function statesAsJSON() {
	global $STATES_STR;
	foreach ( $STATES_STR as $key => $value ) {
		$arr [] = array (
				"key" => $key,
				"message" => $value 
		);
	}
	return json_encode ( $arr );
}
?>