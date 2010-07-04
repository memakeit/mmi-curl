<?php defined('SYSPATH') or die('No direct script access.');

// cURL configuration
return array
(
	'curl_options' => array
	(
			CURLOPT_CLOSEPOLICY		=> CURLCLOSEPOLICY_LEAST_RECENTLY_USED, // int
			CURLOPT_CONNECTTIMEOUT	=> 5,		// int
			CURLOPT_FILETIME		=> TRUE,	// bool
			CURLOPT_FOLLOWLOCATION	=> TRUE,	// bool
			CURLOPT_FRESH_CONNECT	=> FALSE,	// bool
			CURLOPT_HEADER			=> TRUE,	// bool
			CURLOPT_MAXREDIRS		=> 5,		// int
			CURLOPT_NOSIGNAL		=> TRUE,	// bool
			CURLOPT_RETURNTRANSFER	=> TRUE,	// bool
			CURLOPT_SSL_VERIFYHOST	=> 1,		// int
			CURLOPT_SSL_VERIFYPEER	=> FALSE,	// bool
			CURLOPT_TIMEOUT			=> 30,		// int
			CURLOPT_USERAGENT		=> 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6',
			CURLOPT_VERBOSE			=> FALSE
	),
	'http_headers' => array
	(
			'Expect'		=> '',
			'Connection'	=> 'close',
	)
);
