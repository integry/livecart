<?php

$ret = array();

foreach (new DirectoryIterator(ClassLoader::getRealPath('library/tracking.method')) as $method)
{
	if ($method->isFile() && substr($method->getFileName(), 0, 1) != '.')
	{
		$ret[] = basename($method->getFileName(), '.php');
	}
}

return implode(', ', $ret);

?>