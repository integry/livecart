<?php

// setup test database
require_once('init/SetupTestDb.php');
SetupTestDb('server', 'root', '', 'K-shop', 'livecart_test');

// load/import classes
require_once('init/LoadClasses.php');

abstract class BSModel extends ActiveRecordModel {}

ActiveRecord::setDSN("mysql://root@192.168.1.6/livecart_test");

// start application
//require_once('../helper/StartApplication.php');

require_once('UTStandalone.php');

?>