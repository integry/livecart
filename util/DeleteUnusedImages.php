<?php

include '../application/Initialize.php';
ClassLoader::import('application.LiveCart');
new LiveCart();

ClassLoader::import('application.model.product.ProductImage');

$dir = ClassLoader::getRealPath('public.upload.productimage');
if (!file_exists($dir))
{
	return false;
}

$ids = array();
foreach (ActiveRecord::getDataBySQL('SELECT ID FROM ProductImage') as $id)
{
	$ids[$id['ID']] = true;
}

chdir($dir);

foreach (glob('*') as $file)
{
	list($id, $foo) = explode('-', $file, 2);
	if (!isset($ids[$id]))
	{
		unlink($file);
	}
}

?>
Image cleanup completed