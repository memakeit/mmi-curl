_Me Make It cURL Module_
======================

This module provides a wrapper for cURL HTTP requests.  It is based on Ryan Parman's requestcore: <http://github.com/skyzyx/requestcore>

The various HTTP methods map to the following class methods:

* `delete($url, $parms = NULL)`
* `get($url, $parms = NULL)`
* `head($url, $parms = NULL)`
* `post($url, $parms = NULL)`
* `put($url, $parms = NULL)`

Parallel requests are supported using the following methods:
