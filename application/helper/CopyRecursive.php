<?php

function copyRecursive($source, $dest, $copyCallback=null)
{
	if (is_file($source))
	{
		$c = copy($source, $dest);
		chmod($dest, 0777);
		if ($copyCallback)
		{
			call_user_func($copyCallback,$dest);
		}
		return $c;
	}

	if (!is_dir($dest))
	{
		$oldumask = umask(0);
		mkdir($dest, 0777);
		umask($oldumask);
	}

	$dir = dir($source);
	while (false !== $entry = $dir->read())
	{
		if ($entry == "." || $entry == "..")
		{
			continue;
		}
		if ($dest !== $source.'/'.$entry)
		{
			copyRecursive($source.'/'.$entry,  $dest.'/'.$entry, $copyCallback);
		}
	}
	$dir->close();
	return true;
}

?>