<?php

$router = Router::getInstance();
//$router->connect(":controller/:action/:mode/:id", array(), array("id" => "[0-9]*", "mode" => "create|modify"));
$router->connect(":controller/:action/:id", array(), array("id" => "[0-9]*"));

?>