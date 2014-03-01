<?php

include_once __ROOT__ . '/application/helper/CreateHandleString.php';

$router->add("#^/([a-zA-Z0-9\_\-]+)\-([0-9]+)$#", array("controller" => 'category', "action" => 'index', "id" => 2));
$router->add("#^/([a-zA-Z0-9\_\-]+)\-([0-9]+)/([0-9]+)$#", array("controller" => 'category', "action" => 'index', "id" => 2, "page" => 3));
//$router->add("#^/opportunity/" . $handle . "\-([0-9]+)$#", array("controller" => 'heysuccess', "action" => 'view', "id" => 1));
//$router->add("#^/profile/([0-9]+)$#", array("controller" => 'heysuccess', "action" => 'profile', "id" => 1));

$router->add("/{handle:[\-a-zA-Z0-9]+}.html", array("controller" => "staticPage", "action" => "view"));
//$router->add("/{:controller/:action/{id:[0-9]+}", array("controller" => "staticPage", "action" => "view"));
$router->add("#^/([a-zA-Z0-9\_\-]+)/([a-zA-Z0-9\.\_]+)/([0-9]+)$#", array("controller" => 1, "action" => 2, "id" => 3));

$router->add('/:controller/:action/{[0-9]+}', array(
	'controller' => 1,
	'action' => 2,
	'id' => 3
));

$router->add('/:controller/{[0-9]+}', array(
	'controller' => 1,
	'action' => 'index',
	'id' => 2
));

$router->add('/backend/:controller/:action/:params', array(
	'module' => 'backend',
	'controller' => 1,
	'action' => 2,
	'params' => 3
));

$router->add('/backend/:controller/:action', array(
	'module' => 'backend',
	'controller' => 1,
	'action' => 2,
));

$router->add('/backend/:controller', array(
	'module' => 'backend',
	'controller' => 1
));

$router->add('/backend', array(
	'module' => 'backend',
	'controller' => 'index',
	'action' => 'index'
));

$router->add('/{slug:[a-z\-]+}-{id:[0-9]+}', array(
        'controller' => 'category',
        'action' => 'index'
    ))->setName('category');

function route($object)
{
	if ($object instanceof \category\Category)
	{
		return array('id' => $object->getID(), 'slug' => CreateHandleString::create($object->name()), 'for' => 'category');
	}
}
