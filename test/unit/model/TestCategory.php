<?php

require_once("init.php");

$result = Category::deleteByID(25);

var_dump($result);

?>