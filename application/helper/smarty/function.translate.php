<?php

/**
 * Translates interface text to current locale language
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 */
function smarty_function_translate($params, LiveCartSmarty $smarty)
{
	$application = $smarty->getApplication();
	
	$translation = $application->translate($params['text']);
	$translation = preg_replace('/%([a-zA-Z]*)/e', 'smarty_replace_translation_var(\'\\1\', $smarty)', $translation);

	if ($application->isTranslationMode() && !isset($params['disableLiveTranslation']))
	{
		$file = $application->getLocale()->translationManager()->getFileByDefKey($params['text']);
		$file = '__file_'.base64_encode($file);
		$translation = '<span class="transMode __trans_' . $params['text'].' '. $file .'">'.$translation.'</span>';
	}

	return $translation;
}

function smarty_replace_translation_var($key, $smarty)
{
	return $smarty->_tpl_vars[$key];
}

?>