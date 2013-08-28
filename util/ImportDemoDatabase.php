<?php

include '../application/Initialize.php';
new LiveCart();


$file = ClassLoader::getRealPath('installdata.demo') . '.sql';
//$file = 'alter.sql';

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
		try
		{
			ActiveRecord::executeUpdate($query);
		}
		catch (Exception $e)
		{
			var_dump($e->getMessage());
		}
	}
}

?>