<?php

include '../application/Initialize.php';

ClassLoader::import('application.LiveCart');

new LiveCart();

ClassLoader::import('application.model.category.Category');

Category::recalculateProductsCount();
?>