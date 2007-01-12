<?php

/**
 * Application request routing configuration
 *
 * @package application.configuration.route
 * @author Saulius Rupainis <saulius@integry.net>
 */

$routes = array(
					array("shop/:cathandle-:id", array('controller' => 'category', 'action' => 'index'), array("cathandle" => "[a-z\.]+","id" => "[0-9]+")),
					
					array("backend.help/:id", array('controller' => 'backend.help', 'action' => 'view'), array()),
					array(":controller", array("action" => "index"), array()),
					array(":controller/:action", array(), array()),
					array(":controller/:action/:id", array(), array("id" => "[0-9]+")),
					array(":controller/:action/:mode/:id", array(), array("id" => "[0-9]+", "mode" => "create|modify")),

					// special case for passing a language code as an ID
					array(":controller/:action/:id", array(), array('id' => "[a-zA-Z]{2}")),
					array(":controller/:action/:id", array(), array("id" => "_id_")),
  			   );

$router = Router::getInstance();
foreach ($routes as $route)
{
	$router->connect($route[0], $route[1], $route[2]);
  	$route[2]['requestLanguage'] = "[a-zA-Z]{2}";
  	$router->connect(':requestLanguage/' . $route[0], $route[1], $route[2]);
}

?>