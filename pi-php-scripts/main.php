<?php 

require_once("conf.php");
require_once("dylos.php");
require_once("serial.php");
require_once("http.php");

function main()
{
	$m = new DylosMonitor();
	$m->listen();
}

main();

?>

