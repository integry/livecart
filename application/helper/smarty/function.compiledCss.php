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
function smarty_function_compiledCss($params, LiveCartSmarty $smarty)
{
	$app = $smarty->getApplication();

	if (!$app->isBackend())
	{
		if (!function_exists('smarty_function_includeCss'))
		{
			include_once('function.includeCss.php');
		}

		$files = array('common.css', $smarty->getApplication()->getTheme() . '.css');
		foreach ($files as $file)
		{
			smarty_function_includeCss(array('file' => '/upload/css/' . $file), $smarty);
		}
	}

	$includedStylesheetTimestamp = $smarty->_smarty_vars["INCLUDED_STYLESHEET_TIMESTAMP"];
	$includedStylesheetFiles = $smarty->_smarty_vars["INCLUDED_STYLESHEET_FILES"];

	if ($includedStylesheetFiles)
	{
		uksort($includedStylesheetFiles, 'strnatcasecmp');
	}

	if(isset($params['glue']) && ($params['glue'] == true) && !$smarty->getApplication()->isDevMode() && !$smarty->getApplication()->isTranslationMode() && !$smarty->getApplication()->isCustomizationMode())
	{
		$request = $smarty->getApplication()->getRequest();

		if (isset($params['nameMethod']) && 'hash' == $params['nameMethod'])
		{
			$names = array_values($includedStylesheetFiles);
			sort($names);
			$compiledFileName = md5(implode("\n", $names)) . '.css';
		}
		else
		{
			$compiledFileName = $request->getControllerName() . '-' . $request->getActionName() . '.css';
		}

		$compiledFilePath = ClassLoader::getRealPath('public.cache.stylesheet.') .  $compiledFileName;
		$baseDir = ClassLoader::getRealPath('public.stylesheet.');
		$publicDir = ClassLoader::getRealPath('public.');

		$compiledFileTimestamp = 0;
		if(!is_file($compiledFilePath) || filemtime($compiledFilePath) < $includedStylesheetTimestamp)
		{
			if(!is_dir(ClassLoader::getRealPath('public.cache.stylesheet'))) mkdir(ClassLoader::getRealPath('public.cache.stylesheet'), 0777, true);

			// compile
			$compiledFileContent = "";
			foreach($includedStylesheetFiles as $key => $cssFile)
			{
				$relPath = str_replace($publicDir, '', $cssFile);

				$compiledFileContent .= "\n\n\n/***************************************************\n";
				$compiledFileContent .= " * " . $relPath . "\n";
				$compiledFileContent .= " ***************************************************/\n\n";

				$content = file_get_contents($cssFile);
				$content = str_replace('url(..', 'url(' . dirname($relPath) . '/..', $content);

				$compiledFileContent .= $content;
			}

			$compiledFileContent = preg_replace('/\.(jpg|png|gif|bmp)/', '.$1?' . time(), $compiledFileContent);

			file_put_contents($compiledFilePath, $compiledFileContent);

			if (function_exists('gzencode'))
			{
				file_put_contents($compiledFilePath . '.gz', gzencode($compiledFileContent, 9));
			}
		}
		$compiledFileTimestamp = filemtime($compiledFilePath);

		return '<link href="gzip.php?file=' . $compiledFileName . '&time=' . $compiledFileTimestamp . '" media="screen" rel="Stylesheet" type="text/css"/>';
	}
	else if ($includedStylesheetFiles)
	{
		$includeString = "";
		$publicPath = ClassLoader::getRealPath('public.');

		foreach($includedStylesheetFiles as $cssFile)
		{
			$urlPath = str_replace('\\', '/', str_replace($publicPath, '', $cssFile));
			$includeString .= '<link href="' . $urlPath . '?' . filemtime($cssFile) . '" media="screen" rel="Stylesheet" type="text/css"/>' . "\n";
		}

		return $includeString;
	}
}

?>