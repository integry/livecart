<?php

function smarty_prefilter_config($tplSource, $smarty)
{
	return preg_replace_callback('/{t(::(\w+)){0,1} (.+?)}/', '_translate_to_locale', $tplSource);
}

function _translate_to_locale( $key )
{
	$word = isset($key[3]) ? $key[3] : '';
	$controller = !empty($key[2]) ? $key[2] : false;
	
	$locale = Locale::getCurrentLocale();
	return ($controller ? "$controller => " : '') . $locale->translator()->translate($word);
}
?>