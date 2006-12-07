<?php

function smarty_prefilter_config($tplSource, $smarty)
{
	$source = preg_replace('/{tn (.+?)}/', '{translate text=$1 notranslate=true}', $tplSource);
	$source = preg_replace('/{t (.+?)}/', '{translate text=$1}', $source);
	$source = preg_replace('/{help (.+?)}/', '{link controller=backend.help action=view id=$1}', $source);	
	return $source;
}

?>