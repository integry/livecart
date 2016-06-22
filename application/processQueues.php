<?php

/*
 *    Add as many threads as you need to your crontab like this:
 *    * * * * * /usr/bin/php /public_html/storage/processQueues.php > /dev/null 2>&1
 *
 *    The more threads you have, the faster the emails will be sent, and the higher the
 *    CPU load will be. So please use responsibly!
 */

if (isset($_SERVER['REQUEST_URI']))
{
	$_SERVER['REQUEST_URI'] = dirname($_SERVER['REQUEST_URI']) . '/';
}
else
{
	// retrieve base URL from configuration
	$url = include dirname(__file__) . '/../storage/configuration/url.php';
	$parsed = parse_url($url['url']);
	$_SERVER['HTTP_HOST'] = $parsed['host'];
	$_SERVER['REQUEST_URI'] = $parsed['path'];
	$_SERVER['REWRITE'] = $url['rewrite'];
}

if (isset($_SERVER['REWRITE']) && !$_SERVER['REWRITE'])
{
	//$this->request->set('noRewrite', true);
//	$app->getRouter()->enableURLRewrite($_SERVER['rewrite']);
}

include dirname(__file__) . '/../application/Initialize.php';
$app = new LiveCart();
$app->setDevMode(true);
ClassLoader::import('library.activerecord.ActiveRecord');
ClassLoader::import('application.model.user.User');
ClassLoader::import('application.model.newsletter.*');
ClassLoader::import("application.model.*");
ClassLoader::import('application.model.email.SimpleEmailMessage');
ClassLoader::import('application.model.email.EmailQueue');

$app->loadLanguageFile('Base');
$app->loadLanguageFile('Product');
$app->loadLanguageFile('User');
$app->loadLanguageFile('Order');
$app->loadLanguageFile('Customize');
$app->loadLanguageFile('Frontend');
$app->loadLanguageFile('Custom');

$conn = ActiveRecord::getDBConnection();
$queue = new EmailQueue($app->getConfig());

$startTime = time();
$timeout = 1*60;//1 minute

while (time()-$startTime<$timeout)
{
	$emailObject = $queue->receive();
	if (!$emailObject) break;
	$emailObject->setApplication($app);
	$emailObject->setConfig($app->getConfig());
	$res = $emailObject->send(true);
	if ($res) $queue->remove();
}