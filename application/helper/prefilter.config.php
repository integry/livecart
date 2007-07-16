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
	// translations
    $source = preg_replace('/{tn (.+?)}/', '{translate|escape:"html" text="$1" disableLiveTranslation="true"}', $tplSource);
	$source = preg_replace('/{t ([^\|]+?)}/', '{translate text="$1"}', $source);
	$source = preg_replace('/{t ([^|]+)\|([^}]+)}/', '{capture assign="translation_$1"}{translate text=$1}{/capture}{\$translation_$1|$2}', $source);

	// roles
    $source = preg_replace('/{role ([\w.]+)}/', '{role name="$1"}', $source);

    // template customizations - allow to load from a different source
	$source = preg_replace('/{include file="([-.a-zA-Z0-9\/]+)"}/', '{include file="custom:$1"}', $source);	

	$source = preg_replace('/{block (.+?)}/', '{foreach from=\$$1 item=item key=key}{$item}{/foreach}', $source);

    // help system
	$source = preg_replace('/{help (.+?)}/', '{helpLink id=$1}', $source);
	$source = preg_replace('/{see (.+?)}/', '{helpSeeAlsoItem id=$1}', $source);
	
	/**
        shorthand syntax for form error handling
        
        instead of writing this:
        
        <label for="name">Your Name:</label>        
    	<fieldset class="error">
    		{textfield name="name"}
    		<div class="errorText hidden{error for="name"} visible{/error}">{error for="name"}{$msg}{/error}</div>
    	</fieldset>        
        
        it is possible to write this:
        
        {err for="name"}
            {{label Your Name}}
            {textfield}
        {/err}            
	*/	
    $source = preg_replace('/{{err for="([a-zA-Z0-9_]+)"}}(.*){{label(.*)}}(.*){\/err}/msU', '{{err for="\\1"}}\\2<label for="\\1">\\3</label>\\4{/err}', $source);    
    $source = preg_replace('/{err for="([a-zA-Z0-9_]+)"}(.*){{label(.*)}}(.*){\/err}/msU', '{{err for="\\1"}}\\2<label for="\\1">\\3</label>\\4{/err}', $source);    

    $source = preg_replace('/{{err for="([a-zA-Z0-9_]+)"}}(.*){(calendar|checkbox|filefield|password|radio|selectfield|textfield|textarea)(.*)}(.*){\/err}/msU', '\\2<fieldset class="error">{\\3 name="\\1" \\4}\\5
    <div class="errorText hidden{error for="\\1"} visible{/error}">{error for="\\1"}{$msg}{/error}</div>
    </fieldset>', $source);
    	
	return $source;
}

?>