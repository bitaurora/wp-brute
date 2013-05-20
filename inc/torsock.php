<?php


function tor_new_identity($tor_ip='127.0.0.1', $control_port='9051', $auth_code=''){

        $fp = fsockopen($tor_ip, $control_port, $errno, $errstr, 30);

        if (!$fp) return false; //can't connect to the control port



        fputs($fp, "AUTHENTICATE $auth_code\r\n");

        $response = fread($fp, 1024);

        list($code, $text) = explode(' ', $response, 2);

        if ($code != '250') return false; //authentication failed



        //send the request to for new identity

        fputs($fp, "signal NEWNYM\r\n");

        $response = fread($fp, 1024);

        list($code, $text) = explode(' ', $response, 2);

        if ($code != '250') return false; //signal failed



        fclose($fp);

        return true;

}

function tor_dump_stats($message,$tor_ip='127.0.0.1', $control_port='9051', $auth_code=''){

        $fp = fsockopen($tor_ip, $control_port, $errno, $errstr, 30);

        if (!$fp) return false; //can't connect to the control port



        fputs($fp, "AUTHENTICATE $auth_code\r\n");

        $response = fread($fp, 1024);

        list($code, $text) = explode(' ', $response, 2);

        if ($code != '250') return false; //authentication failed



        //send the request to for new identity

        fputs($fp, $message . "\r\n");

        $response = fread($fp, 1024);

        list($code, $text) = explode(' ', $response, 2);

        //echo $response;



        if ($code != '250'){fclose($fp); return $response;} //signal failed



        fclose($fp);

        return $response;

}

function int2string($int, $numbytes=1)

{

   $str = "";

   for ($i=0; $i < $numbytes; $i++) {

     $str .= chr($int % 256);

     $int = $int / 256;

   }

   return $str;

}



function string2int($str)

{

   $numbytes = strlen($str);

   $int = 0;

   for ($i=0; $i < $numbytes; $i++) {

     $int += ord($str[$i]) * pow(2, $i * 8);

   }

   return $int;

}

/*
$socket = new Torsocket("60.240.241.33",80);
$socket->fwrite("GET / HTTP/1.1\r\nHost: dam.pe\r\nUser-agent: Mozilla\r\nConnection: close\r\n\r\n");

while (1) {
	$line = $socket->fread(1024);
	if ($line) {
		echo $line;
	}
}
*/

class Torsocket{

    protected $sock;

    public $error = false;

    public function __construct($ip, $port, $http = false, $torport = 9150, $torhost = "127.0.0.1"){

        $lf = chr(hexdec("0d")) . chr(hexdec("0a"));

        global $debug;

        $this->sock = fsockopen($torhost, $torport);

        $ipbytes = explode(".", $ip);

        $port = pack("n", (int)$port);

        $ip = pack("C4", $ipbytes[0], $ipbytes[1], $ipbytes[2], $ipbytes[3]);

        fwrite($this->sock, chr(04) . chr(01) . $port . $ip . "\x4d\x4f\x5a\x00");

        $status = fread($this->sock, 8192);

        if(stripos($status, "\x5a")){

            if(@$debug){

                $status = str_replace("\x00", "", $status);

                $status = ord($status);

                $status = dechex($status);

                global $socksdebug;

                if($socksdebug) echo "Connected to socks proxy, status code: 0x" . $status . "\n";

            }

            return true;

        }

        else{

            $status = str_replace("\x00", "", $status);

            $status = ord($status);

            $status = dechex($status);

            global $mode;

            if($mode == 0){

            tor_dump_stats("signal RESTART");

            }

            $this->error = true;

            return false;

        }

    }

    public function fwrite($packet){

        return @fwrite($this->sock, $packet);

    }

    public function fread($length){

        return @fread($this->sock, $length);

    }

    public function fclose(){

        fclose($this->sock);

    }

    public function setTimeout($timeout){

        return stream_set_timeout( $this->sock, $timeout);

    }

    public function getMetadata(){

        return stream_get_meta_data($this->sock);

    }

}

?>