<?php

/**
 * HybridAuth
 * http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
 * (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
 */
// ----------------------------------------------------------------------------------------
//	HybridAuth Config file: http://hybridauth.sourceforge.net/userguide/Configuration.html
// ----------------------------------------------------------------------------------------

return
		array(
			"base_url" => "http://".$_SERVER['HTTP_HOST']."/s-login/start/",
			"providers" => array(
				"Vkontakte" => array(
					"enabled" => true,
					"keys" => array("id" => "5768929", "secret" => "q0pFPhUI865R1w0FEJb4")
				),
				"Google" => array(
					"enabled" => true,
					"keys" => array("id" => "908640080063-lqhjompl1qq4fdtl915mlivnkqvjrkq4.apps.googleusercontent.com", "secret" => "qdMxKC5_B91nedd1ScwmZ52A")
				),
				"Facebook" => array(
					"enabled" => true,
					"keys" => array("id" => "1263792950317680", "secret" => "6e7d83476431eb23cc7cc554ed0ec7f9"),
					"trustForwarded" => false
				),
				"Odnoklassniki" => array(
					"enabled" => true,
					"keys" => array("id" => "1247283712", "key" => "CBALAJFLEBABABABA", "secret" => "201DDAD8A7789211B5F681D0")
				)
			),
			// If you want to enable logging, set 'debug_mode' to true.
			// You can also set it to
			// - "error" To log only error messages. Useful in production
			// - "info" To log info and error messages (ignore debug messages)
			"debug_mode" => false,
			// Path to file writable by the web server. Required if 'debug_mode' is not false
			"debug_file" => "",
);
