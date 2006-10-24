<?php

function smarty_prefilter_config($tplSource, $smarty)
{
	return preg_replace_callback('/{t (.+?)}/', '_translate_to_locale', $tpl_source);
}

function _translate_to_locale( $key )
{
	$locale = Locale::getCurrentLocale();
	return $locale->translator()->translate(isset($key[1]) ? $key[1] : '');
}
?>