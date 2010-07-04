<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Test controller for the mget method.
 *
 * @package		MMI API
 * @author		Me Make It
 * @copyright	(c) 2010 Me Make It
 * @license		http://www.memakeit.com/license
 */
class Controller_Test_Curl_MGet extends Controller
{
	/**
	 * @var boolean turn debugging on?
	 **/
	public $debug = TRUE;

	/**
	 * Test the mget method.
	 *
	 * @return	void
	 */
	public function action_index()
	{
		$responses = MMI_Curl::factory()->debug($this->debug)->mget(array
		(
			'memakeit' => array('url' => 'http://github.com/api/v2/json/user/show/memakeit'),
			'shadowhand' => array('url' => 'http://github.com/api/v2/json/user/show/shadowhand'),
		));

		if (is_array($responses) AND count($responses) > 0)
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
} // End Controller_Test_Curl_MGet
