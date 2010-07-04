Me Make It cURL Module
======================

This module provides a wrapper for cURL HTTP requests.  It is based on Ryan Parman's requestcore: <http://github.com/skyzyx/requestcore>

The various HTTP methods map to the following class methods:

* `delete($url, $parms = NULL)`
* `get($url, $parms = NULL)`
* `head($url, $parms = NULL)`
* `post($url, $parms = NULL)`
* `put($url, $parms = NULL)`

where `$url` is a string and `$parms` is an associative array of request parameters.

Parallel requests are supported using the following methods:

* `mdelete($requests)`
* `mexec($requests)`
* `mget($requests)`
* `mhead($requests)`
* `mpost($requests)`
* `mput($requests)`

where each element in the `$requests` array is an associative array containing one or more of the following keys:

* url
* parms
* http_headers
* curl_options

The `mexec` method requests can contain the following key:

* method

The `mexec` method allows parallel requests to be made using different HTTP methods.
Each array of request settings can be associated with a key (recommended for easier extraction of results):
 > $requests = array
 > (
 >> 'memakeit' => array('method' => 'GET', 'url' => 'user/show/memakeit'),
 >> 'shadowhand' => array('method' => 'GET', 'url' => 'user/show/shadowhand'),
 > );
	 *
	 * or the keys can be ommited:
	 *		$requests = array
	 *		(
	 *			array('method' => 'GET', 'url' => 'user/show/memakeit'),
	 *			array('method' => 'GET', 'url' => 'user/show/shadowhand'),
	 *		);
