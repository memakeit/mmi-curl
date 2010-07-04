# Me Make It cURL Module

This module provides a wrapper for cURL HTTP requests.  It is based on
Ryan Parman's requestcore: <http://github.com/skyzyx/requestcore>

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

where each element in the `$requests` array is an associative array containing
one or more of the following keys:

* url (string)
* parms (associative array)
* http_headers (associative array)
* curl_options (associative array)

The `mexec` method allows parallel requests to be made using different HTTP
methods (GET, POST, etc) by supporting the additional key:

* method (string)

Using `mexec` requests as an example, each array of request settings can be
associated with a key (recommended for easier extraction of results):

	$requests = array
	(
		'memakeit' => array('method' => 'GET', 'url' => 'user/show/memakeit'),
		'shadowhand' => array('method' => 'GET', 'url' => 'user/show/shadowhand'),
	);

or the keys can be ommited:

	$requests = array
	(
		array('method' => 'GET', 'url' => 'user/show/memakeit'),
		array('method' => 'GET', 'url' => 'user/show/shadowhand'),
	);


# Responses
Results are returned as instances of the `MMI_Curl_Response` class.  For parallel
requests, an array of response objects is returned.

Details for a response object are accesesed using the following methods:

* `body()` (string)
* `curl_info()` (associative array)
* `error_msg()` (string)
* `error_num()` (integer)
* `http_headers()` (associative array)
* `http_status_code()` (integer)

If debugging is enabled for the MMI_Curl class (using the public `debug` property),
information about the original request can be obtained using the `request()` method.
The `request()` method returns an associative array with the following keys:

* url (string)
* parms (associative array)
* http_method (string)
* curl_options (associative array)


# Test Controllers
2 simple test controllers are found in `classes/controller/test/curl`.  They can be accessed at:

* _your-server_/test/curl/get
* _your-server_/test/curl/mget
