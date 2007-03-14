<?php

$output = exec("/usr/home/www/script/build.sh", $out, $res);

header('Location: /livecart/build/backend.category');

?>