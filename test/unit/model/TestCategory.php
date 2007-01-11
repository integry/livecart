<?php

require_once("init.php");

$result = Category::deleteByID(23);

var_dump($result);

?>