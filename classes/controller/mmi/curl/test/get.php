<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Test controller for the get method.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Curl_Test_Get extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * Test the get method.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$response = MMI_Curl::factory()->debug($this->debug)->get('http://github.com/api/v2/json/user/show/memakeit');
		if ($response instanceof MMI_Curl_Response)
		{
			// Decode the JSON output
			$response->body(json_decode($response->body(), TRUE));
		}
		$this->request->response = Kohana::debug($response);
	}
} // End Controller_MMI_Curl_Test_Get
