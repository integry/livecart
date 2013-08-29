<?php

ClassLoader::import('application/model/system/CssFile');
ClassLoader::import('application/model/template/Theme');

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
function smarty_function_compiledCss($params, Smarty_Internal_Template $smarty)
{
	$app = $smarty->getApplication();

	if (!$app->isBackend())
	{
		if (!function_exists('smarty_function_includeCss'))
		{
			include_once('function.includeCss.php');
		}

		$last = 1000;
		$files = array('common.css');
		$theme = new Theme($smarty->getApplication()->getTheme(), $app);
		foreach ($theme->getAllParentThemes() as $parentTheme)
		{
			$files[] = CssFile::getTheme($parentTheme) . '.css';
		}

		$files[] = CssFile::getTheme($smarty->getApplication()->getTheme()) . '.css';

		foreach ($files as $file)
		{
			smarty_function_includeCss(array('file' => '/upload/css/' . $file, 'last' => ++$last), $smarty);
		}
	}

	$includedStylesheetTimestamp = $smarty->getGlobal("INCLUDED_STYLESHEET_TIMESTAMP");
	$includedStylesheetFiles = $smarty->getGlobal("INCLUDED_STYLESHEET_FILES");

	if ($includedStylesheetFiles)
	{
		uksort($includedStylesheetFiles, 'strnatcasecmp');
	}

	$out = '';

	if(isset($params['glue']) && ($params['glue'] == true) && !$smarty->getApplication()->isDevMode() && (!$smarty->getApplication()->isCustomizationMode() || $app->isBackend()))
	{
		$request = $smarty->getApplication()->getRequest();

		if (isset($params['nameMethod']) && 'hash' == $params['nameMethod'])
		{
			$names = array_values((array)$includedStylesheetFiles);
			sort($names);
			$compiledFileName = md5(implode("\n", $names)) . '.css';
		}
		else
		{
			$compiledFileName = $request->getControllerName() . '-' . $request->getActionName() . '.css';
		}

		$compiledFilePath = $this->config->getPath('public/cache/stylesheet/') .  $compiledFileName;
		$baseDir = $this->config->getPath('public/stylesheet/');
		$publicDir = $this->config->getPath('public/');

		$compiledFileTimestamp = 0;
		if(!is_file($compiledFilePath) || filemtime($compiledFilePath) < $includedStylesheetTimestamp)
		{
			if(!is_dir($this->config->getPath('public/cache/stylesheet'))) mkdir($this->config->getPath('public/cache/stylesheet'), 0777, true);

			// compile
			$compiledFileContent = "";
			foreach($includedStylesheetFiles as $key => $cssFile)
			{
				$relPath = str_replace($publicDir, '', $cssFile);
				$relPath = str_replace('\\', '/', $relPath);

				$compiledFileContent .= "\n\n\n/***************************************************\n";
				$compiledFileContent .= " * " . $relPath . "\n";
				$compiledFileContent .= " ***************************************************/\n\n";

				$content = file_get_contents($cssFile);

				$pre = array('..', 'http', '/');
				foreach (array("'", '"', '') as $quote)
				{
					foreach ($pre as $i)
					{
						$content = str_ireplace('url(' . $quote . $i, 'url__(' . $quote . $i , $content);
					}

					$content = str_replace('url(' . $quote , 'url(' . $quote . dirname($relPath) . '/', $content);

					foreach ($pre as $i)
					{
						$content = str_replace('url__(' . $quote . $i, 'url(' . $quote . $i, $content);
					}
				}

				$content = str_replace('url(..', 'url(' . dirname($relPath) . '/..', $content);
				$content = str_replace('url(\'..', 'url(\'' . dirname($relPath) . '/..', $content);
				$content = str_replace('url("..', 'url("' . dirname($relPath) . '/..', $content);

				$content = str_replace('upload/css/"../../', '"', $content);

				$compiledFileContent .= $content;
			}

			$compiledFileContent = preg_replace('/\.(jpg|png|gif|bmp)/', '.$1?' . time(), $compiledFileContent);
			$compiledFileContent = preg_replace('/-moz-border-radius\:([ \.a-zA-Z0-9]+);/', '-moz-border-radius: $1; -khtml-border-radius: $1; border-radius: $1; ', $compiledFileContent);

			file_put_contents($compiledFilePath, $compiledFileContent);

			if (function_exists('gzencode'))
			{
				file_put_contents($compiledFilePath . '.gz', gzencode($compiledFileContent, 9));
			}
		}
		$compiledFileTimestamp = filemtime($compiledFilePath);

		$out = '<link href="' . $app->getPublicUrl('gzip.php') . '?file=' . $compiledFileName . '&amp;time=' . $compiledFileTimestamp . '" rel="Stylesheet" type="text/css"/>';
	}
	else if ($includedStylesheetFiles)
	{
		$includeString = "";
		$publicPath = $this->config->getPath('public/');

		foreach($includedStylesheetFiles as $cssFile)
		{
			$urlPath = str_replace('\\', '/', str_replace($publicPath, '', $cssFile));
			$includeString .= '<link href="' . $app->getPublicUrl($urlPath) . '?' . filemtime($cssFile) . '" rel="Stylesheet" type="text/css"/>' . "\n";
		}

		$out = $includeString;
	}

	if ($externalFiles = $smarty->getGlobal("INCLUDED_STYLESHEET_FILES_EXTERNAL"))
	{
		foreach($externalFiles as $cssFile)
		{
			$out .= '<link href="' . $cssFile . '" rel="Stylesheet" type="text/css"/>' . "\n";
		}
	}

	return $out;
}

?>
