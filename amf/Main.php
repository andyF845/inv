<?php
/**
 * MAIN SERVER CODE
 * 29/06/2014
 * Invenoty REST server main class
 * @author andyF845
  */

include_once './amf/config.php';
include_once './amf/errors.php';
include_once './amf/states.php';
include_once './amf/MySQLConnector.php';
include_once './amf/CacheProvider.php';


define('GET_ITEM_ARRAY',	'array');
define('GET_ITEM_JSON',		'json');

class Main {
	public static $useCache = true;
	private static $sql;
	private static $cache;
	private static $raw_act = "get";
	private static $str_show = "all";
	private static $str_code = "";
	private static $str_name = "";
	private static $str_memo = "";
	private static $str_hash = "";
	private static $str_location = "";
	private static $int_state = 0;
	/**
	 * Parses params and returns string result for specified query.
	 * @param array $params - array with request parameters. E.g. $_REQUEST[].
	 * <p>Params is safely initialized into static properties.
	 * @return string - server output data
	 */
	static function execute(array $params) {
		// Connect to mySQL server
		(self::$sql = new MySQLconnector ( SQL_SERVER, SQL_USER, SQL_PASSWORD, SQL_DATABASE )) || (die ( errorCodeAsJSON ( ERR_MYSQL_SERVER_ERROR ) ));
	
		// Create cache provider
		self::$cache = new FileCacheProvider();
		self::$cache->active = self::$useCache;
	
		// Init params
		self::initParams ( $params );
	
		// Pars params
		try {
			return self::parsParams();
		} catch ( Exception $e ) {
			return errorCodeAsJSON ( $e->getMessage );
		}
	}
	/**
	 * Returns $sqlQuery result from cache (if any) or from database.
	 * @param string $cacheKey
	 * @param string $sqlQuery
	 * @param bool $jsonArray - if true, json array is returned anyway, elsewise return is based on query result 
	 * @return string - result of query.
	 */
	private static function get($cacheKey,$sqlQuery,$jsonArray = false) {
		if (!$result = self::$cache->get($cacheKey)) {
			$defaultReturnValue = $jsonArray? '[]' : '';
			$result = self::$sql->getJSONResult ($sqlQuery, $defaultReturnValue, $jsonArray );
			self::$cache->set($cacheKey,$result);
		}
		return $result;		
	}
	/**
	 * Returns all items stored in specified location.
	 * @param string $location
	 * @return string
	 */
	private static function getAll($location) {
		return $result = self::get(	"all@$location",
							"SELECT * FROM data WHERE (location like '$location%') AND (state<>" . STATE_DELETED . ") ORDER BY location;",
							true);
	}
	/**
	 * Returns all items having problems in specified location.
	 * @param string $location
	 */
	private static function getProblems($location) {
		return self::get(	"problems@$location",
							"SELECT * FROM data WHERE (location like '$location%') AND (state<>" . STATE_OK . ") AND (state<>" . STATE_DELETED . ") ORDER BY location,code;",
							true );
	}
	/**
	 * Returns all unique locations.
	 * @return string
	 */
	private static function getLocations() {
		return self::get(	'locations',
							"SELECT location FROM data WHERE (state<>" . STATE_DELETED . ")GROUP BY location ORDER BY location ASC;",
							true );
	}
	/**
	 * Returns item data as Array or JSON-string.
	 * @param $code
	 * @param $returnType GET_ITEM_ARRAY or GET_ITEM_JSON
	 * @return array or string based on $returnType
	 */
	private static function getItem($code, $returnType = GET_ITEM_JSON) {
		if (!$result = self::$cache->get("code$code")) {
			$query = "SELECT * FROM data WHERE code='$code' LIMIT 1;";
			$result = self::$sql->getJSONResult ( $query );
			self::$cache->set("code$code", $result);
		}
		if ($returnType == GET_ITEM_ARRAY) {
			$result = json_decode($result, true);
		}
		return $result;
	}
	/**
	 * Inserts or updates item in database.
	 */
	private static function insertItem($code, $name, $memo, $location, $hash, $state) {
		self::$cache->deleteKeys(array('all@','locations',"all@$location","problems@$location","code$code"));
		return self::$sql->goSQL ( "INSERT INTO data VALUES ('$code','$name','$memo','$location','$hash',$state) ON DUPLICATE KEY UPDATE name='$name',memo='$memo',location='$location',hash='$hash',state=$state;" );
	}
	/**
	 * Marks item as deleted in database.
	 */
	private static function deleteItem($code) {
		$location = self::$sql->getArrayResult("SELECT location FROM data WHERE code='$code';");
		$location = $location['location'];
		self::$cache->deleteKeys(array('all@','locations',"all@$location","problems@$location","code$code"));
		
		echo "UPDATE data SET state = " . STATE_DELETED . " WHERE code='$code' LIMIT 1;";
		return self::$sql->goSQL ( "UPDATE data SET state = " . STATE_DELETED . " WHERE code='$code' LIMIT 1;" );
	}
	/**
	 * Deletes items marked as "deleted" from database.
	 */
	private static function realdeleteItems() {
		return self::$sql->goSQL ( "DELETE FROM data WHERE state = " . STATE_DELETED . ";" );
	}
	/**
	 * Returns Hash string for item data stored in object properties.
	 */
	private static function clientDataHash() { 
		return md5 ( self::$str_code . self::$str_name . self::$str_memo . self::$str_location . self::$int_state );
	}
	/**
	 * Inserts or updates item in database using sync mechanism.
	 * There could be performed one of actions:
	 * a) server item is replaced with client data (if server item hasn't change since last client sync)
	 * b) server item is sent to client with prompt to merge (if server item has been updated since last client sync)
	 * @return string JSON data for inserted item
	 */
	private static function setItem() {
		if ((self::$str_code == '') || (self::$str_name == ''))
			return errorCodeAsJSON ( ERR_BAD_DATA );
			
		$item = self::getItem ( self::$str_code, GET_ITEM_ARRAY );
		print_r ($item);
					
		if (self::$str_hash != $item ['hash']) {
			// Item changed on server since last client sync
			// Adding merge field
			$item ['merge'] = true; 
			return json_encode ( $item );
		} else {
			// Fast-forward
			if (self::insertItem (	self::$str_code,
									self::$str_name,
									self::$str_memo,
									self::$str_location,
									self::clientDataHash(),
									self::$int_state) )	{
				return self::getItem ( self::$str_code, GET_ITEM_JSON );
			} else {
				return errorCodeAsJSON ( ERR_MYSQL_INSERT_FAIL );
			}
		}
	}
	/**
	 * Initializes object static properties with given $param data. 
	 * @param array $params
	 */
	private static function initParams(array $params) {
		$vars = get_class_vars ( Main );
		foreach ( $vars as $var => $default ) {
			if (list ( $type, $name ) = explode ( "_", $var ))
				switch ($type) {
					case 'raw' :
						self::$$var = isSet ( $params [$name] ) ? $params [$name] : $default;
						break;
					case 'str' :
						self::$$var = isSet ( $params [$name] ) ? self::$sql->escapeString ( $params [$name] ) : $default;
						break;
					case 'int' :
						self::$$var = isSet ( $params [$name] ) ? ( int ) $params [$name] : $default;
						break;
				}
		}
	}
	/**
	 * Returns string result for specified client request.
	 * Request data is taken from object properties.
	 */
	private static function parsParams() {
		switch (self::$raw_act) {
			// get
			case 'get' :
				switch (self::$str_show) {
					case 'all' :
						$res = self::getAll ( self::$str_location );
						break;
					case 'problems' :
						$res = self::getProblems ( self::$str_location );
						break;
					case 'states' :
						$res = statesAsJSON ();
						break;
					case 'locations' :
						$res = self::getLocations ();
						break;
					default :
						$res = self::getItem ( self::$str_show ? self::$str_show : self::$str_code, GET_ITEM_JSON );
				}
				return $res ? $res : errorCodeAsJSON ( ERR_NOT_FOUND );
			// set
			case 'set' :
				return self::setItem();
			// delete
			case 'del' :
				if (self::$str_code == '')
					return errorCodeAsJSON ( ERR_BAD_DATA );
				return errorCodeAsJSON ( (self::deleteItem ( self::$str_code ) ? ERR_OK : ERR_MYSQL_DELETE_FAIL) );
			// cleanup
			case 'cleanup' :
				return errorCodeAsJSON ( self::realdeleteItems () ? ERR_OK : ERR_MYSQL_DELETE_FAIL );
			// unknown command
			default :
				return errorCodeAsJSON ( ERR_UNKNOWN_COMMAND );
		}
	}
}
?>