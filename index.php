<?php
header("Content-type: application/json; charset=utf-8");

include './amf/Main.php';

echo Main::execute($_REQUEST);
?>
