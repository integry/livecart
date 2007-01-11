<?

	require_once("../../../../framework/ClassLoader.php");

	ClassLoader::mountPath(".", "C:\\projects\\livecart\\");
	ClassLoader::import("application.model.ActiveRecordModel");
	ClassLoader::import("application.model.system.*");
	ClassLoader::import("application.model.category.*");
	ClassLoader::import("application.model.product.*");
	ClassLoader::import("library.activerecord.ActiveRecord");

	ActiveRecordModel::setDSN("mysql://root@192.168.1.6/livecart_test");

?>