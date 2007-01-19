<?php

/**
 * Initial template prefilter (insert additional prefilter to this function)
 *
 * @param string $tplSource
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper
 */
function smarty_prefilter_config($tplSource, $smarty)
{
	$source = preg_replace('/{tn (.+?)}/', '{translate text="$1" notranslate=true}', $tplSource);
	
	$source = preg_replace('/{t ([^\|]+?)}/', '{translate text=$1}', $source);
	
	$source = preg_replace('/{t ([^|]+)\|([^}]+)}/', '{capture assign="translation_$1"}{translate text=$1}{/capture}{\$translation_$1|$2}', $source);
	$source = preg_replace('/{help (.+?)}/', '{link controller=backend.help action=view id=$1}', $source);

	$source = preg_replace('/{block (.+?)}/', '{foreach from=\$$1 item=item key=key}{$item}{/foreach}', $source);

	return $source;
}

?>