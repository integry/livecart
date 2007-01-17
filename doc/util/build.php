<?php

$output = exec("/home/www/cart/build.sh");

header('Location: /cart/build/backend.category');

?>