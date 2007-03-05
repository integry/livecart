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

	if (!$liveTranslation || isset($params['disableLiveTranslation']))
	{
		$translation = Store::getInstance()->translate($params['text']);
	}
	else
	{
		$store = Store::getInstance();
		$translation = $store->translate($params['text']);
		$file = $store->getLocaleInstance()->translationManager()->getFileByDefKey($params['text']);
		$file = '__file_'.base64_encode($file);
		$translation = '<span class="transMode __trans_' . $params['text'].' '. $file .'">'.$translation.'</span>';
	}

	return $translation;
}

?>