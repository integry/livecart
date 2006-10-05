<?php

$router = Router::getInstance();
$router->connect(":controller/:action/:id", array(), array("id" => "[0-9]*"));

?>