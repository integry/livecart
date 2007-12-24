<?php

/**
 *  Custom template file handler
 *
 *  Each template file can have 1 or 2 copies - the original template file (not customized) and user-customized
 *  template file. The goal is to avoid the default view file modifications as it can make updates more difficult
 *  (accidently overwritten customizations, etc.), also there would be no need to make the original templates
 *  directory writable to enable template modification from admin interface.
 *
 *  @package application.helper.smarty
 *  @author Integry Systems
 */

function smarty_resource_custom_source($tpl_name, &$tpl_source, LiveCartSmarty $smarty)
{
	if ($path = smarty_custom_get_path($tpl_name, $smarty))
	{
		$tpl_source = $smarty->processPlugins(file_get_contents($path), $tpl_name);
		return true;
	}

	return false;
}

function smarty_resource_custom_timestamp($tpl_name, &$tpl_timestamp, LiveCartSmarty $smarty_obj)
{
	if ($file = smarty_custom_get_path($tpl_name, $smarty_obj))
	{
		$tpl_timestamp = filemtime($file);
		return true;
	}

	return false;
}

function smarty_custom_get_path($tpl_name, LiveCartSmarty $smarty)
{
	$path = null;

	if ('@' != substr($tpl_name, 0, 1))
	{
		$path = $smarty->getApplication()->getRenderer()->getTemplatePath($tpl_name);
	}

	// absolute path
	else
	{
		$tpl_name = substr($tpl_name, 1);
		foreach (array(ClassLoader::getRealPath('storage.customize.view.'), ClassLoader::getRealPath('application.view.')) as $path)
		{
			$file = $path . $tpl_name;
			if (file_exists($file))
			{
				$path = $file;
			}
		}
	}

	return $path;
}

function smarty_resource_custom_secure($tpl_name, LiveCartSmarty $smarty_obj)
{
	// assume all templates are secure
	return true;
}

function smarty_resource_custom_trusted($tpl_name, LiveCartSmarty $smarty_obj)
{
	// not used for templates
}

?>