# MMI Curl Responses

Results are returned as instances of the `MMI_Curl_Response` class. For parallel
requests, an array of response objects is returned.

Details for a response object are accesesed using the following methods:

* `body()` (string)
* `curl_info()` (associative array)
* `error_msg()` (string)
* `error_num()` (integer)
* `http_headers()` (associative array)
* `http_status_code()` (integer)

If debugging is enabled for the MMI_Curl class (set using the public `debug` property),
information about the original request can be obtained using the `request()` method.
The `request()` method returns an associative array with the following keys:

* url (string)
* params (associative array)
* http_method (string)
* curl_options (associative array)
