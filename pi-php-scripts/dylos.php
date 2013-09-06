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

	function listen()
	{
		$s = "";
		while (1)
		{	
	        $v = $this->port->readPort();
	        $this->log("read: ->'".print_r($v,true)."'");
	        if (!($v==false || $v==""))
	        {
	        	$v = trim($v);
	        	$v = explode(",",$v);
	        	if (count($v)==2)
	        	{
	        		$this->upload($v[0],$v[1]);
	        	}
		    }
		    sleep(30);
	    }
	}

	function upload( $v1, $v2 )
	{
		$iDylosReaderConf::$sensorId ;
		$url = "http://aqicn.info/sensor/";

		$time = time();
		$data = array(
					array("t"=>$time,"v"=>$v1,"id"=>1),
					array("t"=>$time,"v"=>$v2,"id"=>2)
					);

        $post = array( 
        		"key"=>DylosReaderConf::$sensorKey,
        		"id"=>DylosReaderConf::$sensorId,
        		/* Memory information is sent to track memory leaks */
                "mem"=>memory_get_usage(), 
        		"data"=>$data,
                );
^

		$res = Http::post($url,json_encode($post));

		$filename = "/tmp/upload.pending.json";
		if ($res!="ok")
		{
			$this->log("Postponning '".json_encode($post)."' -> $res");
    		$list = file_exists($filename)?json_decode(file_get_contents($filename)):array();
		    file_put_contents($filename,json_encode(array_merge($list,$data)));
		}
		else
		{
			$this->log("Posted '".json_encode($post)."' -> $res");
			if (file_exists($filename))
			{
				$data= json_decode(file_get_contents($filename));
				$post = array( "key"=>"abc","id"=>$id,"data"=>$data );
				$res = syncPost($url,json_encode($post));
				$this->log("Reposting '...' -> $res");
				unlink($filename);
			}
		}
	}

	function log( $msg )
	{
		print($msg."\n");
	}
}

?>

