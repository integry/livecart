<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');


include '../application/Initialize.php';

ClassLoader::import('application.LiveCart');

new LiveCart();

ClassLoader::import('application.model.category.Category');

Category::getInstanceById(1);

Category::recalculateProductsCount();
?>