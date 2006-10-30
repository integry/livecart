<?php

ClassLoader::import("library.activerecord.ActiveRecord");
ClassLoader::import("application.model.*");
ClassLoader::import("application.model.locale.*");

ActiveRecord::$creolePath = ClassLoader::getRealPath("library.creole");

//ActiveRecord::setDSN("mysql://root@192.168.1.6/K-shop");
ActiveRecord::setDSN("mysql://root@192.168.1.6/livecart_dev");

/**
 * Base class for all ActiveRecord based models of application (single entry point in 
 * application specific model class hierarchy)
 *
 * @package application.model
 */
abstract class ActiveRecordModel extends ActiveRecord 
{
}

?>
