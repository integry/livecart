<?php
/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_includeJs($params, LiveCartSmarty $smarty)
{
	static $jsPath;
	if (!$jsPath)
	{
		$jsPath = ClassLoader::getRealPath('public.javascript.');
	}

	$fileName = $params['file'];
	$filePath = substr($fileName, 0, 1) != '/' ?
					$jsPath .  $fileName :
					ClassLoader::getRealPath('public') .  $fileName;

	$fileName = substr($fileName, 0, 1) != '/' ? 'javascript/' . $fileName : substr($fileName, 1);

	//  fix slashes
	$filePath = str_replace('\\', DIRECTORY_SEPARATOR, $filePath);
	$filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);

	if (isset($params['path']))
	{
		$filePath = $params['path'];
	}

	if(!is_file($filePath) || (substr($filePath, -3) != '.js'))
	{
		return;
	}

	if(isset($params['inline']) && $params['inline'] == 'true')
	{
		return '<script src="' . str_replace(DIRECTORY_SEPARATOR, '/', $fileName) . '?' . filemtime($filePath) . '" type="text/javascript"></script>' . "\n";
	}
	else
	{
		$includedJavascriptTimestamp = $smarty->_smarty_vars["INCLUDED_JAVASCRIPT_TIMESTAMP"];
		if(!($includedJavascriptFiles = $smarty->_smarty_vars['INCLUDED_JAVASCRIPT_FILES']))
		{
		   $includedJavascriptFiles = array();
		}

		if(isset($includedJavascriptFiles[$filePath]))
		{
			if (!isset($params['front']))
			{
				return false;
			}
			else
			{
				unset($includedJavascriptFiles[$filePath]);
			}
		}

		$fileMTime = filemtime($filePath);
		if($fileMTime > (int)$includedJavascriptTimestamp)
		{
			$smarty->_smarty_vars['INCLUDED_JAVASCRIPT_TIMESTAMP'] = $fileMTime;
		}

		if(isset($params['front']))
		{
			$includedJavascriptFiles = array_merge(array($filePath => $fileName), $includedJavascriptFiles);
		}
		else
		{
			$includedJavascriptFiles[$filePath] = $fileName;
		}

		$smarty->_smarty_vars['INCLUDED_JAVASCRIPT_FILES'] = $includedJavascriptFiles;
	}
}
?>