<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Test controller for the mget method.
 *
 * @package		MMI Curl
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_MMI_Curl_Test_MGet extends Controller_MMI_Curl_Test
{
	/**
	 * Test the mget method.
	 *
	 * @access	public
	 * @return	void
	 */
	public function action_index()
	{
		$responses = MMI_Curl::factory()->debug($this->debug)->mget(array
		(
			'memakeit' => array('url' => 'https://github.com/api/v2/json/user/show/memakeit'),
			'shadowhand' => array('url' => 'https://github.com/api/v2/json/user/show/shadowhand'),
		));

		if ($responses)
		{
			foreach ($responses as $key => $response)
			{
				if ($response instanceof MMI_Curl_Response)
				{
					// Decode the JSON output
					$responses[$key]->body(json_decode($responses[$key]->body(), TRUE));
				}
			}
		}
		$this->request->response = Kohana::debug($responses);
	}
} // End Controller_MMI_Curl_Test_MGet
