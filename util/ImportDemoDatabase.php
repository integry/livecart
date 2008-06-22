<?php

include '../application/Initialize.php';
ClassLoader::import('application.LiveCart');
new LiveCart();

ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.system.Installer');

$file = ClassLoader::getRealPath('installdata.demo') . '.sql';

if (!file_exists($file))
{
	die('File not found: <strong>' . $file . '</strong>');
}

set_time_limit(0);

Product::getInstanceBySKU('test');

$dump = file_get_contents($file);

// newlines
$dump = str_replace("\r", '', $dump);

// clear comments
$dump = preg_replace('/#.*#/', '', $dump);

// get queries
$queries = preg_split('/;\n/', $dump);

foreach ($queries as $query)
{
	$query = trim($query);
	if (!empty($query))
	{
		ActiveRecord::executeUpdate($query);
	}
}

//echo strlen(file_get_contents($file));
//Installer::loadDatabaseDump(file_get_contents($file));

?>