<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Object representation of a cURL response.
 * This class is based on Ryan Parman's requestcore library.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @copyright	(c) 2006-2010 Ryan Parman, Foleeo Inc., and contributors. All rights reserved.
 * @license		http://www.memakeit.com/license
 * @link		http://github.com/skyzyx/requestcore
 */
class Kohana_MMI_Curl_Response
{
	/**
	 * @var mixed the response body returned by cURL
	 **/
	protected $_body;

	/**
	 * @var array an associative array of the options returned by cURL
	 **/
	protected $_curl_info;

	/**
	 * @var boolean turn debugging on?
	 **/
	protected $_debug;

	/**
	 * @var string the error message returned by cURL
	 **/
	protected $_error_msg;

	/**
	 * @var integer the error number returned by cURL
	 **/
	protected $_error_num;

	/**
	 * @var array the HTTP response headers returned by cURL
	 **/
	protected $_http_headers;

	/**
	 * @var integer the HTTP status code returned by cURL
	 **/
	protected $_http_status_code;

	/**
	 * @var array an associative array containing details of the last cURL request
	 **/
	protected $_request;

	/**
	 * Initialize debugging (using the Request instance).
	 *
	 * @return	void
	 */
	public function __construct()
	{
		$this->_debug = (isset(Request::instance()->debug)) ? (Request::instance()->debug) : (FALSE);
	}

	/**
	 * Get or set the response body returned by cURL.
	 * This method is chainable when setting a value.
	 *
	 * @param	mixed	the value to set
	 * @return	mixed
	 */
	public function body($value = NULL)
	{
		if (func_num_args() === 0)
		{
			return $this->_body;
		}
		return $this->_set('_body', $value);
	}

	/**
	 * Get or set an associative array of the options returned by cURL.
	 * This method is chainable when setting a value.
	 *
	 * @param	array	the value to set
	 * @return	mixed
	 */
	public function curl_info($value = NULL)
	{
		if (func_num_args() === 0)
		{
			return $this->_curl_info;
		}
		return $this->_set('_curl_info', $value, 'is_array');
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
	 * Get or set the error message returned by cURL.
	 * This method is chainable when setting a value.
	 *
	 * @param	string	the value to set
	 * @return	mixed
	 */
	public function error_msg($value = NULL)
	{
		if (func_num_args() === 0)
		{
			return $this->_error_msg;
		}
		return $this->_set('_error_msg', $value, 'is_string');
	}

	/**
	 * Get or set the error number returned by cURL.
	 * This method is chainable when setting a value.
	 *
	 * @param	integer	the value to set
	 * @return	mixed
	 */
	public function error_num($value = NULL)
	{
		if (func_num_args() === 0)
		{
			return $this->_error_num;
		}
		return $this->_set('_error_num', $value, 'is_int');
	}

	/**
	 * Get or set the HTTP headers returned by cURL.
	 * This method is chainable when setting a value.
	 *
	 * @param	array	the value to set
	 * @return	mixed
	 */
	public function http_headers($value = NULL)
	{
		if (func_num_args() === 0)
		{
			return $this->_http_headers;
		}
		return $this->_set('_http_headers', $value, 'is_array');
	}

	/**
	 * Get or set the HTTP status code returned by cURL.
	 * This method is chainable when setting a value.
	 *
	 * @param	integer	the value to set
	 * @return	mixed
	 */
	public function http_status_code($value = NULL)
	{
		if (func_num_args() === 0)
		{
			return $this->_http_status_code;
		}
		return $this->_set('_http_status_code', $value, 'is_int');
	}

	/**
	 * Get or set an associative array containing details of the last cURL request
	 * This method is chainable when setting a value.
	 *
	 * @param	array	the value to set
	 * @return	mixed
	 */
	public function request($value = NULL)
	{
		if (func_num_args() === 0)
		{
			return $this->_request;
		}
		return $this->_set('_request', $value, 'is_array');
	}

	/**
	 * Create a cURL response instance.
	 *
	 * @return	MMI_Curl_Response
	 */
	public static function factory()
	{
		return new MMI_Curl_Response;
	}

	/**
	 * Set a class property.  This method is chainable.
	 *
	 * @param	string	the name of the class property to set
	 * @param	mixed	the value to set
	 * @param	string	the name of the data verification method
	 * @return	MMI_Curl
	 */
	protected function _set($name, $value = NULL, $verify_method = NULL)
	{
		if ( ! empty($verify_method) AND $verify_method($value))
		{
			$this->$name = $value;
		}
		elseif (isset($value))
		{
			$this->$name = $value;
		}
		return $this;
	}
} // End Kohana_MMI_Curl_Response
