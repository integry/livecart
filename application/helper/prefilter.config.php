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
	$source = preg_replace('/{tn (.+?)}/', '{translate|escape:"html" text="$1" disableLiveTranslation="true"}', $tplSource);
	$source = preg_replace('/{t ([^\|]+?)}/', '{translate text="$1"}', $source);
	$source = preg_replace('/{t ([^|]+)\|([^}]+)}/', '{capture assign="translation_$1"}{translate text=$1}{/capture}{\$translation_$1|$2}', $source);

	$source = preg_replace('/{role ([\w.]+)}/', '{role name="$1"}', $source);

	$source = preg_replace('/{include file="([-.a-zA-Z0-9\/]+)"}/', '{include file="custom:$1"}', $source);	

	$source = preg_replace('/{block (.+?)}/', '{foreach from=\$$1 item=item key=key}{$item}{/foreach}', $source);

	$source = preg_replace('/{help (.+?)}/', '{helpLink id=$1}', $source);
	$source = preg_replace('/{see (.+?)}/', '{helpSeeAlsoItem id=$1}', $source);
	
	return $source;
}

?>