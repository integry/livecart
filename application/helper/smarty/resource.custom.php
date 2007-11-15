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
	$paths = custom_get_paths($tpl_name, $smarty);

	foreach ($paths as $path)
	{
		if (file_exists($path))
		{
			$tpl_source = $smarty->processPlugins(file_get_contents($path), $tpl_name);
			return true;
		} 
	}
	
	return false;
}

function smarty_resource_custom_timestamp($tpl_name, &$tpl_timestamp, LiveCartSmarty $smarty_obj)
{
	$paths = custom_get_paths($tpl_name, $smarty_obj);
	  
	foreach ($paths as $path)
	{
		if (file_exists($path))
		{
			$tpl_timestamp = filemtime($path);
			return true;
		} 
	}
	
	return false;
}

function custom_get_paths($tpl_name, LiveCartSmarty $smarty)
{
	static $customDirectory = null;
	
	if (!$customDirectory)
	{
		$customDirectory = ClassLoader::getRealPath('storage.customize.view') . '/';
	}
	
	$paths = array();
	$paths[] = $customDirectory . $tpl_name;
	$paths[] = $smarty->template_dir . '/' . $tpl_name;

	return $paths;	
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