<?php

require_once("init.php");

$result = Category::deleteByID(20);

var_dump($result);

?>