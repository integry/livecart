<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');


include '../application/Initialize.php';


new LiveCart();


$group = UserGroup::getInstanceByID(1, true);
$group->setAllRoles();
$group->save();


?>