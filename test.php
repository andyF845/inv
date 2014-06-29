<?php
header ( "Content-type: text/html; charset=utf-8" );
require_once './amf/Main.php';
function getResult($request, $field = null) {
	$res = Main::execute ( $request );
	$res = json_decode ( $res, true );
	return $field ? $res [$field] : $res;
}
function showTestResult($passed, $msg) {
	echo "<br>";
	echo $passed ? "PASS" : "<b>FAIL</b>";
	echo " - $msg\n\r";
	if (! $passed)
		die ( '<br><b>Test aborted!</b>' );
}
function testIfServerHasTestData($testdata, $hash = null) {
	$request = array (
			'act' => 'get',
			'show' => 'all' 
	);
	$result = getResult ( $request );
	$c = 0;
	foreach ( $result as $line => $data ) {
		if ($data ['code'] == $testdata ['code']) {
			if ($hash) {
				$testdata ['hash'] = $hash;
			}
			$found = ($data == $testdata);
		} else {
			$c ++;
		}
	}
	
	showTestResult ( $found, " testdata found, and $c other records." );
}

/**
 * *TEST 1 - Find unique code**
 */

$request = array (
		'act' => 'get',
		'show' => rand () 
);

$c = 1;
while ( getResult ( $request, 'error' ) != '11' ) {
	$request ['show'] = rand ();
	$c ++;
	if ($c > 50) {
		$c = false;
		break;
	}
}

showTestResult ( $c, "find unique code in <50 steps, took $c step(s). Unique code is " . $request ['show'] . "." );

/**
 * *TEST 2 - Insert data for unique code**
 */
$request ['act'] = 'set';

// Test data
$testdata = array (
		'code' => $request ['show'],
		// 'name' => 'Test item Тестовый элемент',
		'name' => 'Test item',
		// 'memo' => 'Текстовый комментарий для элемента',
		'memo' => 'Test memo',
		'location' => 'Test location',
		'hash' => null,
		'state' => 10 
);

// Setting request fields
unset ( $request ['show'] );
$request += $testdata;

// Performing request
$result = getResult ( $request );

$testdata ['hash'] = md5 ( $testdata ['code'] . $testdata ['name'] . $testdata ['memo'] . $testdata ['location'] . $testdata ['state'] );

showTestResult ( $result == $testdata, "set test data" );

testIfServerHasTestData ( $testdata );

/**
 * TEST3 Modify data
 */
$testdata ['name'] = 'other name';
$testdata ['memo'] = 'other memo';
$testdata ['location'] = 'other location';
$testdata ['state'] = '23';

$request = array (
		'act' => 'set' 
) + $testdata;
print_r(getResult ( $request));
showTestResult ( getResult ( $request, 'name' ) == $testdata ['name'], 'update record.' );

$result = getResult ( $request );
print_r($result);
showTestResult ( $result ['merge'] == true, ' merge test.' );

$testdata ['hash'] = $result ['hash'];
$testdata ['memo'] = "memo merged with server data\n\r" . $request ['memo'];

$request = array (
		'act' => 'set' 
) + $testdata;
$result = getResult ( $request );
print_r($result);
showTestResult ( $result ['memo'] == $testdata ['memo'], ' merge test.' );

testIfServerHasTestData ( $testdata, $result ['hash'] );

// Locations test
$request = array (
		'act' => 'get',
		'show' => 'locations' 
);
$result = getResult ( $request );
$c = 0;
foreach ( $result as $location ) {
	if ($location ['location'] == $testdata ['location']) {
		$found = true;
	} else {
		$c ++;
	}
}
showTestResult ( $found, "check locations output. Found testdata location and $c other location(s)." );

// Delete test
$request = array (
		'act' => 'del',
		'code' => $testdata ['code'] 
);
showTestResult ( getResult ( $request, 'error' ) == '10', 'delete record.' );

// Delete-for-shure test
$request = array (
		'act' => 'get',
		'show' => $testdata ['code'] 
);
showTestResult ( getResult ( $request, 'state' ) == '61', 'check if record was marked as deleted.' );

// States test
$request = array (
		'act' => 'get',
		'show' => 'states' 
);
$result = getResult ( $request );
/*echo "<pre>";
print_r ( $result );
echo "</pre>";*/
showTestResult ( $result, 'states test.' );

$request = array (
		'act' => 'cleanup',
);
$result = getResult ( $request,'error' );
showTestResult ( $result == '10', 'cleanup.' );

showTestResult ( true, '<b>Everything is ok!</b>' );
?>