<?php
/**
 * This filrer replaces smarty {t} tag with current locale translation
 *
 * @param string $tpl_source Not compilded template as string
 * @param Smarty $smarty Smarty instance
 * @return string Not compiled template with {t} tags replaces with translations
 */
function smarty_prefilter_t($tpl_source, $smarty)
{
	return preg_replace_callback('/{t (.+?)}/', '_translate_to_locale', $tpl_source);
}

function _translate_to_locale( $key )
{
	$locale = Locale::getCurrentLocale();
	return $locale->translator()->translate(isset($key[1]) ? $key[1] : '');
}

?>