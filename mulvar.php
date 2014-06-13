<?php
/**
 * Safe initialize vars from $_REQUEST[] data.<p>
 * Assign array data to static fields of <i>VarInit</i> class and call ::init method.
 * */
class VarInit {
	public static $raw_vars = array ();
	public static $str_vars = array ();
	public static $int_vars = array ();
	
	/**
	 * Init global raw variables using names passed
	 * as param with data from $_REQUEST[].
	 * Variables will contain exact data from $_REQUEST[].
	 *
	 * @param
	 *        	<b>vars</b> <i>array</i>
	 *        	array of names of global variables
	 * @return bool Returns true on success or false on failure.
	 */
	static private function initRaw($vars) {
		// init raw params (no parsing)
		foreach ( $vars as $value ) {
			global $$value;
			$$value = $_REQUEST [$value];
		}
		return true;
	}
	
	/**
	 * Init global string variables using names passed as param with data from $_REQUEST[].
	 * Variables will contain sql-safe string data from $_REQUEST[].
	 *
	 * @param
	 *        	<b>vars</b> <i>array</i>
	 *        	array of names of global variables
	 * @param
	 *        	<b>sql</b> <i>MySQLConnector</i>
	 *        	instance of mySQLConnector to run escapeString()
	 * @return bool Returns true on success or false on failure.
	 */
	static private function initStr($vars, $sql) {
		// init str params (mySQLEscapeString)
		foreach ( $vars as $value ) {
			global $$value;
			$$value = $sql->escapeString ( $_REQUEST [$value] );
		}
		return true;
	}
	
	/**
	 * Init global int variables using names passed as param with data from $_REQUEST[].
	 * Variables will contain int data from $_REQUEST[]. If data is not valid, variable will contain 0.
	 *
	 * @param
	 *        	<b>vars</b> <i>array</i>
	 *        	array of names of global variables
	 * @return bool Returns true on success or false on failure.
	 */
	static private function initInt($vars) {
		// init int params
		foreach ( $vars as $value ) {
			global $$value;
			$$value = is_numeric ( $_REQUEST [$value] ) ? $_REQUEST [$value] : 0;
		}
		return true;
	}
	
	/**
	 * Init global variables using names passed as static fields of this class with parsed data from $_REQUEST[].
	 *
	 * @param
	 *        	<b>sql</b> <i>MySQLConnector</i>
	 *        	instance of mySQLConnector to run escapeString()
	 * @return bool Returns true on success or false on failure.
	 */
	static public function init($sql) {
		if (self::$int_vars) {
			self::initInt ( self::$int_vars );
		}
		if (self::$raw_vars) {
			self::initRaw ( self::$raw_vars );
		}
		if (self::$str_vars) {
			self::initStr ( self::$str_vars, $sql );
		}
		return true;
	}
}
?>