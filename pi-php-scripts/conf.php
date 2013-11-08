<?php 

class DylosReaderConf
{
	static $serialPort  = "/dev/ttyUSB0";

	static $sensorId = "xxx";

	/* The key is only needed in case you want to own the ID.
	 * For instance for the sensor ID "dylos.china.beijing.sanltiun",
	 * if you do not provide the right key, then you will not be able
	 * to upload any data.
	 * If you want to get your own ID/key pair, please contact the aqicn
	 * team (aqicn.org/contact/), with the subject 'sensor key request' 
	 */
	static $sensorKey = "";
}

?>

