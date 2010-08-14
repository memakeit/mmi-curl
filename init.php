<?php defined('SYSPATH') or die('No direct script access.');

// Test routes
if (Kohana::$environment !== Kohana::PRODUCTION)
{
	Route::set('mmi/curl/test', 'mmi/curl/test/<controller>(/<action>)')
	->defaults(array
	(
		'directory'	=> 'mmi/curl/test',
	));
}
