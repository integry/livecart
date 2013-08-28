<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');


include '../application/Initialize.php';


new LiveCart();


Category::getInstanceById(1);

Category::recalculateProductsCount();
?>