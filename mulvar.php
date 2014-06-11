<?php
/***Safe initialize vars from $_REQUEST data***/
function initVars($sql) {
	//get params
	$raw_vars = array ('act');
	$str_vars = array ('show', 'memo', 'name', 'code', 'location');
	$int_vars = array ('state');

	//init raw params (no parsing)
	array_walk($raw_vars,create_function('$value,$key', 'global $$value; $$value = $_REQUEST [$value];'));

	//init str params (mySQLEscapeString)
	array_walk($str_vars,create_function('$value,$key,$sql', 'global $$value; $$value = $sql->escapeString ( $_REQUEST [$value] );'),$sql);

	//init int params
	array_walk($int_vars,create_function('$value,$key', 'global $$value; $$value = is_numeric ( $_REQUEST [$value] )? $_REQUEST [$value] : 0;'));
}
?>