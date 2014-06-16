<?php 

class DylosMonitor
{
	var $port;

	function __construct()
	{
		$this->init();
	}	

	function init()
	{
		$this->port = $serial = new phpSerial;

		$p = DylosReaderConf::$serialPort;
		$this->log("Opening $p");
		$serial->deviceSet($p);
		$serial->confBaudRate(9600);
		$serial->confParity("none");
		$serial->confCharacterLength(8);
		$serial->confStopBits(1);
		$serial->confFlowControl("none");

		// Then we need to open it
		$serial->deviceOpen();
	}

	function restartDylos()
	{
		if (isset(DylosReaderConf::$powerRelayGPIO))
		{
			$gpio = DylosReaderConf::$powerRelayGPIO;
			/* Try to switch the relay on and off */
			print("Switching dylos off\n");
			exec("echo 1 > /sys/class/gpio/gpio${gpio}/value");
			sleep(20);
			print("Switching dylos on\n");
			exec("echo 0 > /sys/class/gpio/gpio${gpio}/value");
		}
	}

	function listen()
	{
		$s = "";
		$this->restartDylos();
		$noupdatecount = 0;
		$this->retryUpload=0;
		while (1)
		{	
			$noupdatecount++;
	        	$v = $this->port->readPort();
	        	$this->log("read: ->'".print_r($v,true)."'");
	        	if (!($v==false || $v==""))
	        	{
	        		$v = trim($v);
	        		$v = explode(",",$v);
	        		if (count($v)==2 && is_numeric($v[0]) && is_numeric($v[1]))
	        		{
	        			$this->upload($v[0],$v[1]);
					$noupdatecount = 0;
	        		}
		    	}
			
			/* If no update for 5 minute, restart the dylos */
			if ($noupdatecount>3)
			{
				print("No update for ".($noupdatecount/2)." minutes\n");
			}
			if ($noupdatecount>2*5 /* 5 minutes */)
			{
				$this->restartDylos();
				/* Try to switch the relay on and off */
				$noupdatecount = 0;
			}
		    	sleep(30);
	    	}
	}

	function upload( $v1, $v2 )
	{
		$url = "http://sensor.aqicn.org/sensor/";

		$time = time();
		$data = array(
			array("t"=>$time,"v"=>$v1,"id"=>1),
			array("t"=>$time,"v"=>$v2,"id"=>2)
			);

        	$post = array( 
        		"key"=>DylosReaderConf::$sensorKey,
        		"id"=>DylosReaderConf::$sensorId,
        		/* For server backward compatiblity */
        		"clientVersion"=>2,
			/* Keep track of the local IP adress for debugging */
                        "ip"=>exec("/sbin/ifconfig wlan0 | grep 'inet addr:' | cut -d: -f2"),
        		/* Memory information is sent to track memory leaks */
                	"mem"=>memory_get_usage(), 
        		"data"=>$data,
                );

		$res = Http::post($url,json_encode($post));
		$json = json_decode($res); 

		$filename = "/tmp/upload.dylos.pending.json";
		if (!isset($json->result) || $json->result!="ok")
		{
			$this->log("Postponning '".json_encode($post)."'\nServer says '$res'\n");
    			$list = file_exists($filename)?json_decode(file_get_contents($filename)):array();
			file_put_contents($filename,json_encode(array_merge($list,$data)));
		}
		else
		{
			$this->log("Posted '".json_encode($post)."'.\nServer said '$res'\n");
			if (file_exists($filename))
			{
				$data= json_decode(file_get_contents($filename));
				$post = array( 
	        			"key"=>DylosReaderConf::$sensorKey,
	        			"id"=>DylosReaderConf::$sensorId,
	        			/* For server backward compatiblity */
	        			"clientVersion"=>2,
	        			/* Memory information is sent to track memory leaks */
	                		"mem"=>memory_get_usage(), 
					"data"=>$data 
				);
				$res = Http::post($url,json_encode($post));
				$this->log("Reposting ... Server says '$res'\n");
				$json = json_decode($res);
				if (!isset($json->result) || $json->result!="ok") 
				{
					/* In case the upload does not work, try 3 times before failing */
					$this->retryUpload ++;
					if ($this->retryUpload<3) return;

					$time = time();
					$path = realpath(dirname(__FILE__))."/logs/";
					mkdir($path);
					$tmpfile = $path."errlog.dylos.failed.".$time;
					file_put_contents($tmpfile,$res);
					$tmpfile = $path."upload.dylos.failed.".$time;
					file_put_contents($tmpfile,json_encode($data));
				}
				unlink($filename);
				$this->retryUpload=0;
			}
		}
	}

	function log( $msg )
	{
		print($msg."\n");
	}
}

?>

