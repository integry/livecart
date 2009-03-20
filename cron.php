<?php

/**
 *  Front controller for LiveCart scheduled tasks
 *
 *  @author Integry Systems
 */

if (isset($_SERVER['REQUEST_URI']))
{
	$_SERVER['REQUEST_URI'] = dirname($_SERVER['REQUEST_URI']) . '/';
}
else
{
	// retrieve base URL from configuration
	$url = include dirname(__file__) . '/storage/configuration/url.php';
	$parsed = parse_url($url['url']);
	$_SERVER['HTTP_HOST'] = $parsed['host'];
	$_SERVER['REQUEST_URI'] = $parsed['path'];
	$_SERVER['REWRITE'] = $url['rewrite'];
}

include dirname(__file__) . '/application/Initialize.php';
$app = new LiveCart();
if (isset($_SERVER['rewrite']))
{
	$app->getRouter()->enableURLRewrite($_SERVER['rewrite']);
}
$app->getCron()->process();

?>