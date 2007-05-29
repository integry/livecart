<?php

/**
 * Application request routing configuration
 *
 * @package application.configuration.route
 * @author Integry Systems
 */

$routes = array(					
                    // category URLs
                    array("shop/:cathandle-:id", array('controller' => 'category', 'action' => 'index'), array("cathandle" => "[a-z0-9\.]+","id" => "[0-9]+")),
					array("shop/:cathandle-:id/:page", array('controller' => 'category', 'action' => 'index'), array("cathandle" => "[a-z0-9\.]+","id" => "[0-9]+","page" => "[0-9_]+")),

					array("shop/:cathandle-:id/:filters", array('controller' => 'category', 'action' => 'index'), array("cathandle" => "[a-z0-9\.]+","id" => "[0-9]+", "filters" => "([,]{0,1}[0-9A-Za-z._]+\-[vmps]{0,1}[0-9]{0,})*")),
					array("shop/:cathandle-:id/:filters/:page", array('controller' => 'category', 'action' => 'index'), array("cathandle" => "[a-z0-9\.]+","id" => "[0-9]+","page" => "[0-9_]+", "filters" => "[0-9A-Za-z\-.,_]+")),
					
					// help section
                    array("help/:id", array('controller' => 'help', 'action' => 'view'), array()),
					
                    // static pages
                    array(":handle.html", array('controller' => 'staticPage', 'action' => 'view'), array("handle" => "[a-zA-Z0-9\.]+")),

					// default rules
                    array(":controller", array("action" => "index"), array()),
					array(":controller/:id", array("action" => "index"), array("id" => "-?[0-9]+")),
					array(":controller/:action", array(), array()),
					array(":controller/:action/:id", array(), array("id" => "-?[0-9]+")),
					array(":controller/:action/:mode/:id", array(), array("id" => "[0-9]+", "mode" => "create|modify")),

					// special case for passing a language code as an ID
					array(":controller/:action/:id", array(), array('id' => "[a-zA-Z]{2}")),
					array(":controller/:action/:id", array(), array("id" => "_id_")),

					// product pages
                    array(":producthandle-:id", array('controller' => 'product', 'action' => 'index'), array("producthandle" => "[a-z0-9\.]+","id" => "[0-9]+")),
                    
                    
  			   );

$router = Router::getInstance();
foreach ($routes as $route)
{
	$router->connect($route[0], $route[1], $route[2]);
  	$route[2]['requestLanguage'] = "[a-zA-Z]{2}";
  	$router->connect(':requestLanguage/' . $route[0], $route[1], $route[2]);
}

?>