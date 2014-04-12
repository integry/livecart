<?php

include_once __ROOT__ . '/application/helper/CreateHandleString.php';

$handle = '([^\.\047]{0,})';

$router->add("#^/" . $handle . "\-([0-9]+)$#", array("controller" => 'category', "action" => 'index', "id" => 2));
$router->add('/' . $handle . '-{id:[0-9]+}/{page:[_0-9]+}', array("controller" => 'category', "action" => 'index', "slug" => 1))->setName('category-page');
//$router->add("#^/opportunity/" . $handle . "\-([0-9]+)$#", array("controller" => 'heysuccess', "action" => 'view', "id" => 1));
//$router->add("#^/profile/([0-9]+)$#", array("controller" => 'heysuccess', "action" => 'profile', "id" => 1));

$router->add("/{handle:[\-a-zA-Z0-9]+}.html", array("controller" => "staticPage", "action" => "view"))->setName('staticpage');
//$router->add("/{:controller/:action/{id:[0-9]+}", array("controller" => "staticPage", "action" => "view"));
$router->add("#^/([a-zA-Z0-9\_\-]+)/([a-zA-Z0-9\.\_]+)/([\-0-9]+)$#", array("controller" => 1, "action" => 2, "id" => 3));

$router->add('/backend', array(
	'module' => 'backend',
	'controller' => 'index',
	'action' => 'index'
));

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

$router->add('/' . $handle . '-{id:[0-9]+}', array(
        'slug' => 1,
        'controller' => 'category',
        'action' => 'index'
    ))->setName('category');

$router->add('/shop/' . $handle . '-{id:[0-9]+}', array(
        'slug' => 1,
        'controller' => 'product',
        'action' => 'index'
    ))->setName('product');

function route($object, $params = array())
{
	$route = array();
	
	if ($object instanceof \category\Category)
	{
		$route = array('id' => $object->getID(), 'slug' => CreateHandleString::create($object->name()), 'for' => 'category');
	}
	
	else if ($object instanceof \product\Product)
	{
		$route = array('id' => $object->getID(), 'slug' => CreateHandleString::create($object->name()), 'for' => 'product');
	}

	else if ($object instanceof \staticpage\StaticPage)
	{
		$route = array('id' => $object->getID(), 'handle' => CreateHandleString::create($object->handle), 'for' => 'staticpage');
	}
	
	$route = array_merge($route, $params);
	
	return $route;
}
