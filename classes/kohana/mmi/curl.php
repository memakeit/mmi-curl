<?php defined('SYSPATH') or die('No direct script access.');
/**
 * cURL helper.
 * This class is based on Ryan Parman's requestcore library.
 *
 * @package		MMI Curl
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @copyright	(c) 2006-2010 Ryan Parman, Foleeo Inc., and contributors. All rights reserved.
 * @license		http://www.memakeit.com/license
 * @link		https://github.com/skyzyx/requestcore
 */
class Kohana_MMI_Curl
{
	/**
	 * @var Kohana_Config cURL settings
	 */
	protected static $_config;

	/**
	 * @var array an associative array mapping each cURL constant to a string representation of its name
	 */
	protected static $_curl_constants_map;

	/**
	 * @var array cURL version information
	 */
	protected static $_version_info;

	/**
	 * @var array an associative array of cURL options
	 **/
	protected $_curl_options = array();

	/**
	 * @var boolean turn debugging on?
	 **/
	protected $_debug;

	/**
	 * @var array an associative array of HTTP headers
	 **/
	protected $_http_headers = array();

	/**
	 * @var array an associative array of proxy settings
	 **/
	protected $_proxy;

	/**
	 * @var array an associative array for mapping requests to responses (used for debugging)
	 **/
	protected $_requests = array();

	/**
	 * Ensure the cURL PHP extension is loaded.
	 * Initialize debugging (using the Request instance).
	 * Load the configuration settings.
	 *
	 * @return	void
	 * @uses	MMI_Log::log_error
	 * @uses	MMI_Request::debug
	 */
	public function __construct()
	{
		// Ensure the cURL PHP extension is loaded
		if ( ! function_exists('curl_init'))
		{
			$msg = 'The php_curl extension is required';
			if (class_exists('MMI_Log'))
			{
				MMI_Log::log_error(__METHOD__, __LINE__, $msg);
			}
			throw new Kohana_Exception($msg);
		}

		$this->_debug = class_exists('MMI_Request') ? MMI_Request::debug() : FALSE;

		// Load cURL options and HTTP headers from the config file
		$config = self::get_config();
		$this->_curl_options = $config->get('curl_options', array());
		$this->_http_headers = $config->get('http_headers', array());
	}

	/**
	 * Get or set whether debugging is enabled.
	 * This method is chainable when setting a value.
	 *
	 * @param	mixed	the value to set
	 * @return	mixed
	 */
	public function debug($value = NULL)
	{
		if (func_num_args() === 0)
		{
			return $this->_debug;
		}
		return $this->_set('_debug', $value, 'is_bool');
	}

	/**
	 * Get or set the proxy details used by cURL requests.
	 * This method is chainable when setting a value.
	 *
	 * @param	mixed	an associative array of proxy settings or
	 *					a proxy url string in the following format: 'proxy://user:pass@hostname:port'
	 * @return	mixed
	 */
	public function proxy($value = NULL)
	{
		if (func_num_args() === 0)
		{
			return $this->_proxy;
		}
		if (is_string($value))
		{
			$value = parse_url($value);
		}
		return $this->_set('_proxy', $value, 'is_array');
	}

	/**
	 * Add a cURL option.
	 * This method is chainable.
	 *
	 * @param	string	the option name
	 * @param	mixed	the option value
	 * @return	MMI_Curl
	 */
	public function add_curl_option($name, $value)
	{
		$this->_curl_options[$name] = $value;
		return $this;
	}

	/**
	 * Remove a cURL option.
	 * This method is chainable.
	 *
	 * @param	string	the option name
	 * @return	MMI_Curl
	 */
	public function remove_curl_option($name)
	{
		if (array_key_exists($name, $this->_curl_options))
		{
			unset($this->_curl_options[$name]);
		}
		return $this;
	}

	/**
	 * Remove all the cURL options.
	 * This method is chainable.
	 *
	 * @return	MMI_Curl
	 */
	public function clear_curl_options()
	{
		$this->_curl_options = array();
		return $this;
	}

	/**
	 * Reset the cURL options to the configuration defaults.
	 * This method is chainable.
	 *
	 * @return	MMI_Curl
	 */
	public function reset_curl_options()
	{
		$this->_curl_options = self::get_config()->get('curl_options', array());
		return $this;
	}

	/**
	 * Get or set the cURL options. Set operations overwrite the existing cURL options.
	 * This method is chainable when setting a value.
	 *
	 * @param	array	an associative array of cURL options
	 * @return	mixed
	 */
	public function curl_options($options = NULL)
	{
		if (func_num_args() === 0)
		{
			return $this->_curl_options;
		}
		return $this->_set('_curl_options', $options, 'is_array');
	}

	/**
	 * Add an HTTP header to the cURL request.
	 * This method is chainable.
	 *
	 * @param	string	the header name
	 * @param	mixed	the header value
	 * @return	MMI_Curl
	 */
	public function add_http_header($name, $value)
	{
		$this->_http_headers[$name] = $value;
		return $this;
	}

	/**
	 * Remove an HTTP header from the cURL request.
	 * This method is chainable.
	 *
	 * @param	string	the header name
	 * @return	MMI_Curl
	 */
	public function remove_http_header($name)
	{
		if (array_key_exists($name, $this->_http_headers))
		{
			unset($this->_http_headers[$name]);
		}
		return $this;
	}

	/**
	 * Remove all the HTTP headers.
	 * This method is chainable.
	 *
	 * @return	MMI_Curl
	 */
	public function clear_http_headers()
	{
		$this->_http_headers = array();
		return $this;
	}

	/**
	 * Reset the HTTP headers to the configuration defaults.
	 * This method is chainable.
	 *
	 * @return	MMI_Curl
	 */
	public function reset_http_headers()
	{
		$this->_http_headers = self::get_config()->get('http_headers', array());
		return $this;
	}

	/**
	 * Get or set the HTTP headers for the cURL request. Set operations overwrite the existing HTTP headers.
	 * This method is chainable when setting a value.
	 *
	 * @param	array	an associative array of HTTP headers
	 * @return	mixed
	 */
	public function http_headers($options = NULL)
	{
		if (func_num_args() === 0)
		{
			return $this->_http_headers;
		}
		return $this->_set('_http_headers', $options, 'is_array');
	}

	/**
	 * Make a DELETE request.
	 *
	 * @param	string	the URL
	 * @param	array	an associative array of request parameters
	 * @return	MMI_Curl_Response
	 */
	public function delete($url, $params = NULL)
	{
		return $this->_exec($url, $params, MMI_HTTP::METHOD_DELETE);
	}

	/**
	 * Make a GET request.
	 *
	 * @param	string	the URL
	 * @param	array	an associative array of request parameters
	 * @return	MMI_Curl_Response
	 */
	public function get($url, $params = NULL)
	{
		return $this->_exec($url, $params, MMI_HTTP::METHOD_GET);
	}

	/**
	 * Make a HEAD request.
	 *
	 * @param	string	the URL
	 * @param	array	an associative array of request parameters
	 * @return	MMI_Curl_Response
	 */
	public function head($url, $params = NULL)
	{
		return $this->_exec($url, $params, MMI_HTTP::METHOD_HEAD);
	}

	/**
	 * Make a POST request.
	 *
	 * @param	string	the URL
	 * @param	array	an associative array of request parameters
	 * @return	MMI_Curl_Response
	 */
	public function post($url, $params = NULL)
	{
		return $this->_exec($url, $params, MMI_HTTP::METHOD_POST);
	}

	/**
	 * Make a PUT request.
	 *
	 * @param	string	the URL
	 * @param	array	an associative array of request parameters
	 * @return	MMI_Curl_Response
	 */
	public function put($url, $params = NULL)
	{
		return $this->_exec($url, $params, MMI_HTTP::METHOD_PUT);
	}

	/**
	 * Make multiple DELETE requests.
	 * See the mget method for the format of the requests data.
	 *
	 * @see		mget
	 * @param	array	the request details (URL, request parameters, HTTP headers, and cURL options)
	 * @return	array
	 */
	public function mdelete($requests)
	{
		return $this->_mexec($requests, MMI_HTTP::METHOD_DELETE);
	}

	/**
	 * Make multiple GET requests.
	 * Each request is an associative array containing a URL (key = url) and optional request parameters, HTTP headers and cURL options (keys = params, http_headers, curl_options).
	 * Each array of request settings can be associated with a key (recommended for easier extraction of results):
	 *		$requests = array
	 *		(
	 *			'memakeit' => array('url' => 'user/show/memakeit'),
	 *			'shadowhand' => array('url' => 'user/show/shadowhand'),
	 *		);
	 *
	 * or the keys can be ommited:
	 *		$requests = array
	 *		(
	 *			array('url' => 'user/show/memakeit'),
	 *			array('url' => 'user/show/shadowhand'),
	 *		);
	 *
	 * @param	array	the request details (URL, request parameters, HTTP headers, and cURL options)
	 * @return	array
	 */
	public function mget($requests)
	{
		return $this->_mexec($requests, MMI_HTTP::METHOD_GET);
	}

	/**
	 * Make multiple HEAD requests.
	 * See the mget method for the format of the requests data.
	 *
	 * @see		mget
	 * @param	array	the request details (URL, request parameters, HTTP headers, and cURL options)
	 * @return	array
	 */
	public function mhead($requests)
	{
		return $this->_mexec($requests, MMI_HTTP::METHOD_HEAD);
	}

	/**
	 * Make multiple POST requests.
	 * See the mget method for the format of the requests data.
	 *
	 * @see		mget
	 * @param	array	the request details (URL, request parameters, HTTP headers, and cURL options)
	 * @return	array
	 */
	public function mpost($requests)
	{
		return $this->_mexec($requests, MMI_HTTP::METHOD_POST);
	}

	/**
	 * Make multiple PUT requests.
	 * See the mget method for the format of the requests data.
	 *
	 * @see		mget
	 * @param	array	the request details (URL, request parameters, HTTP headers, and cURL options)
	 * @return	array
	 */
	public function mput($requests)
	{
		return $this->_mexec($requests, MMI_HTTP::METHOD_PUT);
	}

	/**
	 * Make multiple HTTP requests.
	 * Each request is an associative array containing an HTTP method (key = method), a URL (key = url) and optional request parameters, HTTP headers and cURL options (keys = params, http_headers, curl_options).
	 * Each array of request settings can be associated with a key (recommended for easier extraction of results):
	 *		$requests = array
	 *		(
	 *			'memakeit' => array('method' => 'GET', 'url' => 'user/show/memakeit'),
	 *			'shadowhand' => array('method' => 'GET', 'url' => 'user/show/shadowhand'),
	 *		);
	 *
	 * or the keys can be ommited:
	 *		$requests = array
	 *		(
	 *			array('method' => 'GET', 'url' => 'user/show/memakeit'),
	 *			array('method' => 'GET', 'url' => 'user/show/shadowhand'),
	 *		);
	 *
	 * @param	array	the request details (HTTP method, URL, request parameters, HTTP headers, and cURL options)
	 * @return	array
	 */
	public function mexec($requests)
	{
		return $this->_mexec($requests);
	}

	/**
	 * Execute a cURL request.
	 *
	 * @param	string	the URL
	 * @param	array	the request parameters
	 * @param	string	the HTTP method
	 * @return	MMI_Curl_Response
	 */
	protected function _exec($url, $params = array(), $http_method = MMI_HTTP::METHOD_GET)
	{
		// Init cURL and set options
		$ch = $this->_init_curl($url, $params, $http_method);

		// Execute the request and process the response
		$response = $this->_process_response($ch, curl_exec($ch), $url, $params);

		// Close the cURL handle
		curl_close($ch);
		unset($ch);

		// Return the response
		return $response;
	}

	/**
	 * Execute multiple cURL requests in parallel.
	 *
	 * @param	string	the URL
	 * @param	array	the request details (URL, request parameters, HTTP headers, and cURL options)
	 * @param	string	the HTTP method
	 * @return	array
	 * @uses	MMI_Log::log_error
	 */
	protected function _mexec($requests, $http_method = NULL)
	{
		// configure the HTTP method
		if ( ! empty($http_method))
		{
			foreach ($requests as $id => $request)
			{
				$requests[$id]['method'] = $http_method;
			}
		}

		// Create the cURL handles
		$handles = array();
		foreach ($requests as $id => $request)
		{
			foreach (array('url', 'params', 'method', 'http_headers', 'curl_options') as $var)
			{
				$$var = Arr::get($request, $var);
			}
			$handles[$id] = $this->_init_curl($url, $params, $method, $http_headers, $curl_options);
		}

		// Create a cURL multi-handle and add the cURL handles to the multi-handle
		$multi = curl_multi_init();
		foreach ($handles as $handle)
		{
			curl_multi_add_handle($multi, $handle);
		}

		// Execute the requests
		do
		{
			$status = curl_multi_exec($multi, $active);
		}
		while ($status == CURLM_CALL_MULTI_PERFORM OR $active);

		// Retrieve the responses
		$responses = array();
		foreach ($handles as $id => $handle)
		{
			$request = $requests[$id];
			$url = Arr::get($request, 'url');
			if (intval(curl_errno($handle)) === CURLE_OK)
			{
				// Process the response
				$params = Arr::get($request, 'params');
				$responses[$id] = $this->_process_response($handle, curl_multi_getcontent($handle), $url, $params);
			}
			else
			{
				if (class_exists('MMI_Log'))
				{
					MMI_Log::log_error(__METHOD__, __LINE__, 'Multi cURL error for URL: '.$url.'. Error number: '.curl_errno($handle).'. Error message: '.curl_error($handle));
				}
			}

			// Close each cURL handle
			curl_multi_remove_handle($multi, $handle);
			curl_close($handle);
		}

		// Close the cURL multi handle
		curl_multi_close($multi);
		unset($multi);

		// Return the responses
		return $responses;
	}

	/**
	 * Create a cURL handle and configure the cURL options (including custom HTTP request headers).
	 *
	 * @param	string	the URL
	 * @param	array	an associative array of request parameters
	 * @param	string	the HTTP method
	 * @param	array	an associative array of custom HTTP headers (to be merged with the defaults)
	 * @param	array	an associative array of custom cURL options (to be merged with the defaults)
	 * @return	resource
	 */
	protected function _init_curl($url, $params = array(), $http_method = MMI_HTTP::METHOD_GET, $http_headers = array(), $curl_options = array())
	{
		// Create a cURL handle
		$ch = curl_init();

		// Save the request details for debugging
		$request = array();
		if ($this->_debug)
		{
			$request['url'] = $url;
			$temp = $params;
			if ( ! is_array($params))
			{
				parse_str($params, $temp);
			}
			$request['params'] = $temp;
		}

		// Encode the request parameters
		if (is_array($params) AND count($params) > 0 AND Arr::is_assoc($params))
		{
			$params = http_build_query($params);
		}

		// Configure the cURL options
		if ( ! is_array($curl_options))
		{
			$curl_options = array();
		}
		$options = Arr::merge($this->_curl_options, $curl_options);
		$options[CURLOPT_URL] = $url;

		set_time_limit
		(
			intval(Arr::get($options, CURLOPT_CONNECTTIMEOUT, 5)) +
			intval(Arr::get($options, CURLOPT_TIMEOUT, 30)) +
			5
		);

		// Configure the proxy connection, if necessary
		$proxy = $this->_proxy;
		if (is_array($proxy) AND count($proxy) > 0)
		{
			$options[CURLOPT_HTTPPROXYTUNNEL] = TRUE;
			$host = $proxy['host'];
			$host .= (isset($proxy['port'])) ? (':'.$proxy['port']) : '';
			$options[CURLOPT_PROXY] = $host;
			if (isset($proxy['user']) AND isset($proxy['pass']))
			{
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->proxy['user'].':'.$this->proxy['pass']);
			}
		}

		// Configure the encoding
		if (extension_loaded('zlib'))
		{
			$options[CURLOPT_ENCODING] = '';
		}

		// Configure the HTTP headers
		if ( ! is_array($http_headers))
		{
			$http_headers = array();
		}
		$http_headers = Arr::merge($this->_http_headers, $http_headers);
		if (is_array($http_headers) AND count($http_headers) > 0)
		{
			$headers = array();
			foreach ($http_headers as $name => $value)
			{
				$headers[] = "{$name}: {$value}";
			}
			$options[CURLOPT_HTTPHEADER] = $headers;
		}

		// Configure HTTP method specific settings
		switch (strtoupper($http_method))
		{
			case MMI_HTTP::METHOD_HEAD:
				$options[CURLOPT_CUSTOMREQUEST] = MMI_HTTP::METHOD_HEAD;
				$options[CURLOPT_NOBODY] = TRUE;
			break;

			case MMI_HTTP::METHOD_GET:
				if ( ! empty($params) AND strpos($url, '?') === FALSE)
				{
					$options[CURLOPT_URL] = $url.'?'.$params;
				}
			break;

			case MMI_HTTP::METHOD_POST:
				$options[CURLOPT_POST] = TRUE;
				$options[CURLOPT_POSTFIELDS] = $params;
			break;

			default:
				$options[CURLOPT_CUSTOMREQUEST] = $http_method;
				$options[CURLOPT_POSTFIELDS] = $params;
			break;
		}

		// Set the cURL options
		foreach ($options as $name => $value)
		{
			curl_setopt($ch, $name, $value);
		}

		// Save the request details for debugging
		if ($this->_debug)
		{
			// Save the request details for debugging
			$request_id = md5(serialize($url.$params));
			$request['http_method'] = $http_method;
			$request['curl_options'] = self::debug_curl_options($options);
			$this->_requests[$request_id] = $request;
		}

		// Return the cURL handle
		return $ch;
	}

	/**
	 * Process the cURL response.
	 *
	 * @param	resource	the cURL handle
	 * @param	string		the cURL response
	 * @param	string		the request URL
	 * @param	array		an associative array of request parameters
	 * @return	mixed
	 * @uses	MMI_Log::log_error
	 * @uses	MMI_Log::log_info
	 */
	protected function _process_response($ch, $response, $url, $params = array())
	{
		if ( ! is_resource($ch))
		{
			if (class_exists('MMI_Log'))
			{
				MMI_Log::log_error(__METHOD__, __LINE__, "Unable to establish cURL connection for URL: {$url}");
			}
			return FALSE;
		}

		if ($response === FALSE)
		{
			// Cannot connect
			$response = FALSE;
			if (class_exists('MMI_Log'))
			{
				MMI_Log::log_error(__METHOD__, __LINE__, "Unable to establish cURL connection for URL: {$url}");
			}
		}
		elseif ($response === TRUE)
		{
			// No response data
			$response = NULL;
			if (class_exists('MMI_Log'))
			{
				MMI_Log::log_info(__METHOD__, __LINE__, "No cURL data for URL: {$url}");
			}
		}
		else
		{
			// Process the HTTP headers and response body
			$curl_info = curl_getinfo($ch);
			$header_size = intval(Arr::get($curl_info, 'header_size'));
			$http_headers = substr($response, 0, $header_size);
			$body = substr($response, $header_size);
			$http_headers = $this->_parse_headers($http_headers);

			// Create the cURL response object
			$response = MMI_Curl_Response::factory()
				->body($body)
				->curl_info($curl_info)
				->error_msg(curl_error($ch))
				->error_num(curl_errno($ch))
				->http_headers($http_headers)
				->http_status_code(intval(Arr::get($curl_info, 'http_code')));

			// Save the request details for debugging
			if ($this->_debug)
			{
				if (is_array($params) AND count($params) > 0 AND Arr::is_assoc($params))
				{
					$params = http_build_query($params);
				}
				$request_id = md5(serialize($url.$params));
				$request = Arr::get($this->_requests, $request_id);
				if (array_key_exists($request_id, $this->_requests))
				{
					unset($this->_requests[$request_id]);
				}
				$response->request($request);
			}
		}
		return $response;
	}

	/**
	 * Parse the HTTP headers returned by cURL.
	 *
	 * @param	string	the HTTP headers
	 * @return	array
	 */
	protected function _parse_headers($http_headers)
	{
		$headers = explode("\r\n\r\n", trim($http_headers));
		$headers = array_pop($headers);
		$headers = explode("\r\n", $headers);
		array_shift($headers);

		// Convert the headers into an associative array
		$http_headers = array();
		foreach ($headers as $header)
		{
			$parts = explode(': ', $header);
			$http_headers[$parts[0]] = $parts[1];
		}
		return $http_headers;
	}

	/**
	 * Set a class property.
	 * This method is chainable.
	 *
	 * @param	string	the name of the class property to set
	 * @param	mixed	the value to set
	 * @param	string	the name of the data verification method
	 * @return	MMI_Curl
	 */
	protected function _set($name, $value = NULL, $verify_method = NULL)
	{
		if (empty($verify_method))
		{
			$this->$name = $value;
		}
		elseif ($verify_method($value))
		{
			$this->$name = $value;
		}
		return $this;
	}

	/**
	 * Create a cURL instance.
	 *
	 * @return	MMI_Curl
	 */
	public static function factory()
	{
		return new MMI_Curl;
	}

	/**
	 * Get the configuration settings.
	 *
	 * @param	boolean	return the configuration as an array?
	 * @return	mixed
	 */
	public static function get_config($as_array = FALSE)
	{
		(self::$_config === NULL) AND self::$_config = Kohana::config('mmi-curl');
		if ($as_array)
		{
			return self::$_config->as_array();
		}
		return self::$_config;
	}

	/**
	 * Get the cURL version information.
	 * If a key is specified, the corresponding value is returned.
	 * Otherwise an associative array of all version information is returned.
	 *
	 * @param	string	the key used to retrieve an individual value
	 * @return	mixed
	 */
	public static function get_version_info($key = NULL)
	{
		(self::$_version_info === NULL) AND self::$_version_info = curl_version();
		$info = self::$_version_info;
		if ( ! empty($key) AND array_key_exists($key, $info))
		{
			$info = Arr::get($info, $key);
		}
		return $info;
	}

	/**
	 * Debug the cURL options by replacing the cURL numeric constants with their 'CURLOPT_' constant names.
	 *
	 * @param	array	the cURL options to debug
	 * @return	array
	 */
	public static function debug_curl_options($options)
	{
		$curl_options = array();
		$curl_constants_map = self::get_curl_constants_map();
		$option_name;
		foreach ($options as $name => $value)
		{
			$option_name = Arr::get($curl_constants_map, $name, $name);
			switch ($name)
			{
				case CURLOPT_HEADERFUNCTION:
//				case CURLOPT_PASSWDFUNCTION:
				case CURLOPT_READFUNCTION:
				case CURLOPT_WRITEFUNCTION:
					if (is_array($value) AND count($value) > 1)
					{
						$value = $value[1];
					}
				break;
			}
			$curl_options[$option_name] = $value;
		}
		return $curl_options;
	}

	/**
	 * Get an associative array mapping each cURL constant to a string representation of its name.
	 *
	 * @return	array
	 */
	public static function get_curl_constants_map()
	{
		(self::$_curl_constants_map === NULL) AND self::$_curl_constants_map = self::_get_curl_constants_map();
		return self::$_curl_constants_map;
	}

	/**
	 * Get an associative array mapping each cURL constant to a string representation of its name.
	 *
	 * @return	array
	 */
	protected static function _get_curl_constants_map()
	{
		return array
		(
			// Boolean values
			CURLOPT_AUTOREFERER => 'CURLOPT_AUTOREFERER',
			CURLOPT_BINARYTRANSFER => 'CURLOPT_BINARYTRANSFER',
			CURLOPT_COOKIESESSION => 'CURLOPT_COOKIESESSION',
			CURLOPT_CRLF => 'CURLOPT_CRLF',
			CURLOPT_DNS_USE_GLOBAL_CACHE => 'CURLOPT_DNS_USE_GLOBAL_CACHE',
			CURLOPT_FAILONERROR => 'CURLOPT_FAILONERROR',
			CURLOPT_FILETIME => 'CURLOPT_FILETIME',
			CURLOPT_FOLLOWLOCATION => 'CURLOPT_FOLLOWLOCATION',
			CURLOPT_FORBID_REUSE => 'CURLOPT_FORBID_REUSE',
			CURLOPT_FRESH_CONNECT => 'CURLOPT_FRESH_CONNECT',
			CURLOPT_FTP_USE_EPRT => 'CURLOPT_FTP_USE_EPRT',
			CURLOPT_FTP_USE_EPSV => 'CURLOPT_FTP_USE_EPSV',
			CURLOPT_FTPAPPEND => 'CURLOPT_FTPAPPEND',
//			CURLOPT_FTPASCII => 'CURLOPT_FTPASCII',
			CURLOPT_FTPLISTONLY => 'CURLOPT_FTPLISTONLY',
			CURLOPT_HEADER => 'CURLOPT_HEADER',
			CURLOPT_HTTPGET => 'CURLOPT_HTTPGET',
			CURLOPT_HTTPPROXYTUNNEL => 'CURLOPT_HTTPPROXYTUNNEL',
//			CURLOPT_MUTE => 'CURLOPT_MUTE',
			CURLOPT_NETRC => 'CURLOPT_NETRC',
			CURLOPT_NOBODY => 'CURLOPT_NOBODY',
			CURLOPT_NOPROGRESS => 'CURLOPT_NOPROGRESS',
			CURLOPT_NOSIGNAL => 'CURLOPT_NOSIGNAL',
			CURLOPT_POST => 'CURLOPT_POST',
			CURLOPT_PUT => 'CURLOPT_PUT',
			CURLOPT_RETURNTRANSFER => 'CURLOPT_RETURNTRANSFER',
			CURLOPT_SSL_VERIFYPEER => 'CURLOPT_SSL_VERIFYPEER',
			CURLOPT_TRANSFERTEXT => 'CURLOPT_TRANSFERTEXT',
			CURLOPT_UNRESTRICTED_AUTH => 'CURLOPT_UNRESTRICTED_AUTH',
			CURLOPT_UPLOAD => 'CURLOPT_UPLOAD',
			CURLOPT_VERBOSE => 'CURLOPT_VERBOSE',

			// Integer values
			CURLOPT_BUFFERSIZE => 'CURLOPT_BUFFERSIZE',
			CURLOPT_CLOSEPOLICY => 'CURLOPT_CLOSEPOLICY',
			CURLOPT_CONNECTTIMEOUT => 'CURLOPT_CONNECTTIMEOUT',
//			CURLOPT_CONNECTTIMEOUT_MS => 'CURLOPT_CONNECTTIMEOUT_MS',
			CURLOPT_DNS_CACHE_TIMEOUT => 'CURLOPT_DNS_CACHE_TIMEOUT',
			CURLOPT_FTPSSLAUTH => 'CURLOPT_FTPSSLAUTH',
			CURLOPT_HTTP_VERSION => 'CURLOPT_HTTP_VERSION',
			CURLOPT_HTTPAUTH => 'CURLOPT_HTTPAUTH',
			CURLOPT_INFILESIZE => 'CURLOPT_INFILESIZE',
			CURLOPT_LOW_SPEED_LIMIT => 'CURLOPT_LOW_SPEED_LIMIT',
			CURLOPT_LOW_SPEED_TIME => 'CURLOPT_LOW_SPEED_TIME',
			CURLOPT_MAXCONNECTS => 'CURLOPT_MAXCONNECTS',
			CURLOPT_MAXREDIRS => 'CURLOPT_MAXREDIRS',
			CURLOPT_PORT => 'CURLOPT_PORT',
//			CURLOPT_PROTOCOLS => 'CURLOPT_PROTOCOLS',
			CURLOPT_PROXYAUTH => 'CURLOPT_PROXYAUTH',
			CURLOPT_PROXYPORT => 'CURLOPT_PROXYPORT',
			CURLOPT_PROXYTYPE => 'CURLOPT_PROXYTYPE',
//			CURLOPT_REDIR_PROTOCOLS => 'CURLOPT_REDIR_PROTOCOLS',
			CURLOPT_RESUME_FROM => 'CURLOPT_RESUME_FROM',
			CURLOPT_SSL_VERIFYHOST => 'CURLOPT_SSL_VERIFYHOST',
			CURLOPT_SSLVERSION => 'CURLOPT_SSLVERSION',
			CURLOPT_TIMECONDITION => 'CURLOPT_TIMECONDITION',
			CURLOPT_TIMEOUT => 'CURLOPT_TIMEOUT',
//			CURLOPT_TIMEOUT_MS => 'CURLOPT_TIMEOUT_MS',
			CURLOPT_TIMEVALUE => 'CURLOPT_TIMEVALUE',

			// String values
			CURLOPT_CAINFO => 'CURLOPT_CAINFO',
			CURLOPT_CAPATH => 'CURLOPT_CAPATH',
			CURLOPT_COOKIE => 'CURLOPT_COOKIE',
			CURLOPT_COOKIEFILE => 'CURLOPT_COOKIEFILE',
			CURLOPT_COOKIEJAR => 'CURLOPT_COOKIEJAR',
			CURLOPT_CUSTOMREQUEST => 'CURLOPT_CUSTOMREQUEST',
			CURLOPT_EGDSOCKET => 'CURLOPT_EGDSOCKET',
			CURLOPT_ENCODING => 'CURLOPT_ENCODING',
			CURLOPT_FTPPORT => 'CURLOPT_FTPPORT',
			CURLOPT_INTERFACE => 'CURLOPT_INTERFACE',
			CURLOPT_KRB4LEVEL => 'CURLOPT_KRB4LEVEL',
			CURLOPT_POSTFIELDS => 'CURLOPT_POSTFIELDS',
			CURLOPT_PROXY => 'CURLOPT_PROXY',
			CURLOPT_PROXYUSERPWD => 'CURLOPT_PROXYUSERPWD',
			CURLOPT_RANDOM_FILE => 'CURLOPT_RANDOM_FILE',
			CURLOPT_RANGE => 'CURLOPT_RANGE',
			CURLOPT_REFERER => 'CURLOPT_REFERER',
			CURLOPT_SSL_CIPHER_LIST => 'CURLOPT_SSL_CIPHER_LIST',
			CURLOPT_SSLCERT => 'CURLOPT_SSLCERT',
			CURLOPT_SSLCERTPASSWD => 'CURLOPT_SSLCERTPASSWD',
			CURLOPT_SSLCERTTYPE => 'CURLOPT_SSLCERTTYPE',
			CURLOPT_SSLENGINE => 'CURLOPT_SSLENGINE',
			CURLOPT_SSLENGINE_DEFAULT => 'CURLOPT_SSLENGINE_DEFAULT',
			CURLOPT_SSLKEY => 'CURLOPT_SSLKEY',
			CURLOPT_SSLKEYPASSWD => 'CURLOPT_SSLKEYPASSWD',
			CURLOPT_SSLKEYTYPE => 'CURLOPT_SSLKEYTYPE',
			CURLOPT_URL => 'CURLOPT_URL',
			CURLOPT_USERAGENT => 'CURLOPT_USERAGENT',
			CURLOPT_USERPWD => 'CURLOPT_USERPWD',

			// Array values
			CURLOPT_HTTP200ALIASES => 'CURLOPT_HTTP200ALIASES',
			CURLOPT_HTTPHEADER => 'CURLOPT_HTTPHEADER',
			CURLOPT_POSTQUOTE => 'CURLOPT_POSTQUOTE',
			CURLOPT_QUOTE => 'CURLOPT_QUOTE',

			// Stream values
			CURLOPT_FILE => 'CURLOPT_FILE',
			CURLOPT_INFILE => 'CURLOPT_INFILE',
			CURLOPT_STDERR => 'CURLOPT_STDERR',
			CURLOPT_WRITEHEADER => 'CURLOPT_WRITEHEADER',

			// Callback values
			CURLOPT_HEADERFUNCTION => 'CURLOPT_HEADERFUNCTION',
//			CURLOPT_PASSWDFUNCTION => 'CURLOPT_PASSWDFUNCTION',
			CURLOPT_READFUNCTION => 'CURLOPT_READFUNCTION',
			CURLOPT_WRITEFUNCTION => 'CURLOPT_WRITEFUNCTION'
		);
	}
} // End Kohana_MMI_Curl
