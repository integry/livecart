<?php

/**
 * Translates interface text to current locale language
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
 */
function smarty_function_translate($params, Smarty $smarty)
{
	$liveTranslation = isset($_SESSION['translationMode']);

	$store = Store::getInstance();
	$translation = $store->translate($params['text']);
	$translation = preg_replace('/%([a-zA-Z]*)/e', 'smarty_replace_translation_var(\'\\1\', $smarty)', $translation);

	if ($liveTranslation && !isset($params['disableLiveTranslation']))
	{
		$file = $store->getLocaleInstance()->translationManager()->getFileByDefKey($params['text']);
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