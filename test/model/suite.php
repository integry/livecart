<?php
require_once '../Initialize.php';

$suite = new UTGroupTest('All livecart test');
$suite->addDir(getcwd());
$suite->run();
?>