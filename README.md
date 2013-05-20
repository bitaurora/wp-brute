

wp-brute.php | Wordpress login brute forcer
================================

Wp-brute is a small php application designed for testing security on your Wordpress based website.

It's core function is to try and gain access to a Wordpress administration account, using a "brute force" nature.

Currently, wp-brute only supports a dictionary based attack, however, the source code can be easily modified to suit other brute force types.

Some key features of wp-brute

* Pure SOCKS connectivity - no need for cURL or other libraries.
* Session logging
* Proxy support for TOR / other SOCKS4 proxy library.
* Timeout and retry support

Recommendations
-------------------------

* Wp-brute performs best on local servers, due to PHP's socket limitations. Always clone your Wordpress site to a local server for higher speeds.
* Remote connections can be dangerous. Some hosting providers will blacklist your IP for sending so many requests to login in a short burst.
* If you are running via proxy, you should manually resolve DNS entries in an anonymous fashion and add them to /etc/hosts (Or similar). SOCKS4 does not support remote name resolution, and thus, can compromise your identity.

How do I use it?
-------------------------

wp-brute.php should be used primarily from the command line, meaning, you will require php5-cli.

	Usage: wp-brute.php wp-loginurl username passlist [[/proxy] 127.0.0.1:9050]
	wp-loginurl: Full url pointing to target wp-login.php
	username: The Wordpress username to attack
	passlist: Location of password dictionary file
	/proxy: Connect via proxy

Here's a couple of examples

A local session with no proxy

	php wp-brute.php http://localhost/blog/wp-login.php admin lib/passlist.txt
	
A remote session through a proxy.

	php wp-brute.php http://site.com/wp-login.php admin lib/passlist.txt /proxy 127.0.0.1:9050

Questions
--------------------

Questions should be pointed to [bitaurora [at] tormail [dot] org] [1]

  [1]: mailto:bitaurora@tormail.org        "bitaurora [at] tormail [dot] org"

