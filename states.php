<?php
//States for client app
define(STATE_OK,10);
define(STATE_BROKEN_HARD,11);
define(STATE_BROKEN_SOFT,21);
define(STATE_MAINTENANCE,31);
define(STATE_PARTS,41);
define(STATE_DISPOSE,51);

$STATES_STR = array( 	
	array( STATE_OK => 'Работает' ),
	array( STATE_BROKEN_HARD => 'Сломан (ПО)' ),
	array( STATE_BROKEN_SOFT => 'Сломан (Железо)'),
	array( STATE_MAINTENANCE => 'Обслуживание'),
    array( STATE_PARTS => 'Запчасть'),
    array( STATE_DISPOSE => 'Выкинуть'));
?>    