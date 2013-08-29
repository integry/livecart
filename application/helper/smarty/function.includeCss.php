<?php


/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application/helper/smarty
 * @author Integry Systems
 */
function smarty_function_includeCss($params, Smarty_Internal_Template $smarty)
{
	$fileName = $params['file'];
	$filePath = substr($fileName, 0, 1) != '/' ?
					$this->config->getPath('public/stylesheet/') .  $fileName :
					$this->config->getPath('public') .  $fileName;

	// fix slashes
	$filePath = str_replace('\\', DIRECTORY_SEPARATOR, $filePath);
	$filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);

	if((!is_file($filePath) && !isset($params['external']))  || (substr($filePath, -4) != '.css'))
	{
		return;
	}

	$css = CssFile::getInstanceFromPath($filePath, $smarty->getApplication()->getTheme());

	$origFileName = $fileName;

	if ($css->isPatched())
	{
		$filePath = $css->getPatchedFilePath();
		$fileName = $css->getPatchedFileRelativePath();
	}

	if(isset($params['inline']) && $params['inline'] == 'true')
	{
		$path = 'stylesheet/' . str_replace(DIRECTORY_SEPARATOR, '/', $fileName) . '?' . filemtime($filePath);
		return '<link href="' . $path . '" media="screen" rel="Stylesheet" type="text/css" />' . "\n";
	}
	else if (isset($params['external']))
	{
		$external = (array)$smarty->getGlobal('INCLUDED_STYLESHEET_FILES_EXTERNAL');
		$external[] = $fileName;
		$smarty->setGlobal('INCLUDED_STYLESHEET_FILES_EXTERNAL', $external);
	}
	else
	{
		$includedStylesheetTimestamp = $smarty->getGlobal('INCLUDED_STYLESHEET_TIMESTAMP');
		if(!($includedStylesheetFiles = $smarty->getGlobal('INCLUDED_STYLESHEET_FILES')))
		{
		   $includedStylesheetFiles = array();
		}

		if(in_array($filePath, $includedStylesheetFiles))
		{
			if (isset($params['front']))
			{
				unset($includedStylesheetFiles[array_search($filePath, $includedStylesheetFiles)]);
			}
			else
			{
				return;
			}
		}

		$fileMTime = filemtime($filePath);
		if($fileMTime > (int)$includedStylesheetTimestamp)
		{
			$smarty->setGlobal('INCLUDED_STYLESHEET_TIMESTAMP', $fileMTime);
		}

		if (isset($params['front']))
		{
			array_unshift($includedStylesheetFiles, $filePath);
		}
		else if (isset($params['last']))
		{
			$includedStylesheetFiles['x' . ((count($includedStylesheetFiles) + 200) * (int)$params['last'])] = $filePath;
		}
		else
		{
			array_push($includedStylesheetFiles, $filePath);
		}

		$smarty->setGlobal('INCLUDED_STYLESHEET_FILES', $includedStylesheetFiles);
	}

	foreach ($smarty->getApplication()->getConfigContainer()->getFilesByRelativePath('public/stylesheet/' . $origFileName, true) as $file)
	{
		if (realpath($file) == realpath($filePath))
		{
			continue;
		}

		$file = substr($file, strlen($this->config->getPath('public')));
		$params['file'] = $file;
		smarty_function_includeCss($params, $smarty);
	}
}
?>