<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');


include '../application/Initialize.php';

ClassLoader::import('application.LiveCart');

new LiveCart();

ClassLoader::import('application.model.user.UserGroup');

$group = UserGroup::getInstanceByID(1, true);
$group->setAllRoles();
$group->save();


?>