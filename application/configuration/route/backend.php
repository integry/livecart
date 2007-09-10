<?php

/**
 * Application request routing configuration
 *
 * @package application.configuration.route
 * @author Integry Systems
 */

$handle = '[^\.\047]{0,}';

$routes = array(					
					// backend
                    array("backend", array('controller' => 'backend.index', 'action' => 'index'), array()),
                    
                    // category URLs
                    array("shop/:cathandle.:id", array('controller' => 'category', 'action' => 'index'), array("cathandle" => $handle, "id" => "[0-9]+")),
					array("shop/:cathandle.:id/:page", array('controller' => 'category', 'action' => 'index'), array("cathandle" => $handle, "id" => "[0-9]+","page" => "[0-9_]+")),

					array("shop/:cathandle.:id/:filters", array('controller' => 'category', 'action' => 'index'), array("cathandle" => $handle, "id" => "[0-9]+", "filters" => "([,]{0,1}[-0-9A-Za-z\.]+\-[vmps]{0,1}[0-9]{0,})*")),
					array("shop/:cathandle.:id/:filters/:page", array('controller' => 'category', 'action' => 'index'), array("cathandle" => $handle, "id" => "[0-9]+","page" => "[0-9_]+", "filters" => "[0-9A-Za-z\-.,_]+?")),
					
                    // static pages
                    array(":handle.html", array('controller' => 'staticPage', 'action' => 'view'), array("handle" => "[a-zA-Z0-9\.]+")),
                    
					// product pages
                    array(":producthandle.:id", array('controller' => 'product', 'action' => 'index'), array("producthandle" => $handle, "id" => "[0-9]+")), 

					// default rules
                    array(":controller", array("action" => "index"), array()),               
                    array(":controller/:id", array("action" => "index"), array("id" => "-?[0-9]+")),				
                    array(":controller/:action", array(), array()),			
                    array(":controller/:action/:id", array(), array("id" => "-?[0-9]+")),			
                    array(":controller/:action/:mode/:id", array(), array("id" => "[0-9]+", "mode" => "create|modify")),

					// special case for passing a language code as an ID
					array(":controller/:action/:id", array(), array('id' => "[a-zA-Z]{2}")),
					array(":controller/:action/:id", array(), array("id" => "_id_")),
                    
                    // checkout
                    array("checkout/:action/:id", array('controller' => 'checkout'), array("id" => "[-A-Za-z0-9]+")), 

                );


// SSL
if ($this->config->get('SSL_PAYMENT'))
{
    $this->router->setSslAction('checkout', 'pay');
    $this->router->setSslAction('backend.payment', 'ccForm');
    $this->router->setSslAction('backend.customerOrder');
    $this->router->setSslAction('backend.CustomerOrder');
}

if ($this->config->get('SSL_CHECKOUT'))
{
    $this->router->setSslAction('checkout');
}

if ($this->config->get('SSL_CUSTOMER'))
{
    $this->router->setSslAction('user');
}

// language index page
$this->router->connect(':requestLanguage', array('controller' => 'index', 'action' => 'index'), array('requestLanguage' => "[a-zA-Z]{2}"));

foreach ($routes as $route)
{
	$this->router->connect($route[0], $route[1], $route[2]);
  	$route[2]['requestLanguage'] = "[a-zA-Z]{2}";
  	$this->router->connect(':requestLanguage/' . $route[0], $route[1], $route[2]);
}

?>