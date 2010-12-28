<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Test controller for the get method.
 *
 * @package		MMI Curl
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Curl_Test_Get extends Controller_MMI_Curl_Test
{
	/**
	 * Test the get method.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		$response = MMI_Curl::factory()->debug($this->debug)->get('https://github.com/api/v2/json/user/show/memakeit');
		if ($response instanceof MMI_Curl_Response)
		{
			// Decode the JSON output
			$response->body(json_decode($response->body(), TRUE));
		}
		$this->request->response = Kohana::debug($response);
	}
} // End Controller_MMI_Curl_Test_Get
