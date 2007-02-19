<?php

echo "<pre>";
require_once("init.php");

ClassLoader::import("application.model.help.*");

$root = HelpTopic::getRootTopic('en');

print_R($root->getSubTopics());

echo "OK\n<br/>";
echo "</pre>";

?>