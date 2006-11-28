<?php

/**
 * Application request routing configuration
 *
 * @package application.configuration.route
 * @author Saulius Rupainis <saulius@integry.net>
 */

$router = Router::getInstance();

$router->connect("backend.help/:id", array('controller' => 'backend.help', 'action' => 'view'));

$router->connect(":controller", array("action" => "index"));
$router->connect(":controller/:action");
$router->connect(":controller/:action/:id", array(), array("id" => "[0-9]+"));

/** special case for passing a language code as an ID **/
$router->connect(":controller/:action/:id", array(), array("id" => "[a-zA-Z]+"));

$router->connect(":controller/:action/:id", array(), array("id" => "%id%"));
$router->connect(":controller/:action/:mode/:id", array(), array("id" => "[0-9]+", "mode" => "create|modify"));

?>