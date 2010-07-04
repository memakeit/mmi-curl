<?php defined('SYSPATH') or die('No direct script access.');

// Test route
if (Kohana::$environment !== Kohana::PRODUCTION)
{
	Route::set('test/curl', 'test/curl/<controller>(/<action>)')
	->defaults(array
	(
		'directory'	=> 'test/curl',
	));
}
