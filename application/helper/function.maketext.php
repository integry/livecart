<?php

/**
 * Creates more complex translation strings that depend on and include numeric variables
 *
 * <code>
 *      {maketext text="There are [quant,_1,item,items,no items] in your shopping basket." params=$cnt}
 *      {maketext text="Displaying [_1] to [_2] of [_3] found orders." params=$from,$to,$count}
 * </code>
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 */
function smarty_function_maketext($params, LiveCartSmarty $smarty) 
{	
	$application = $smarty->getApplication();
	$translation = $application->makeText($params['text'], $params['params']);
	
	if ($application->isTranslationMode() && !isset($params['disableLiveTranslation']))
	{
		$file = $application->getLocale()->translationManager()->getFileByDefKey($params['text']);
		$file = '__file_'.base64_encode($file);
		$translation = '<span class="transMode __trans_' . $params['text'].' '. $file .'">'.$translation.'</span>';
	}
	
	return $translation;
}

?>