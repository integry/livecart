<?php

/**
 * Initial template prefilter (insert additional prefilter to this function)
 *
 * @param string $tplSource
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 *  @author Integry Systems
 */
function smarty_prefilter_config($source, $smarty)
{
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

	$source = preg_replace('/{err for="(.*)"}(^\/err)\<label(.*)\>(.*)\<\/label\>(.*){\/err}/msU', '{{err for="\\1"}}\\2<label\\3><span class="label">\\4</span></label>\\5{/err}', $source);
	$source = preg_replace('/{{1,2}err for="(.*)"}{1,2}(.*)\{\{label (.*)\}\}(.*){\/err}/msU', '{{err for="\\1"}}\\2<label for="\\1"><span class="label">\\3</span></label>\\4{/err}', $source);

	// @todo: user/checkout.tpl doesn't compile correctly
	if (strpos($source, '{label') !== false)
	{
		$source = preg_replace('/{{1,2}err for="(.*)"}{1,2}(.*)\{label (.*)\}(.*){\/err}/msU', '{{err for="\\1"}}\\2<label for="\\1"><span class="label">{t \\3}</span></label>\\4{/err}', $source);
	}

	// replace `backticks` to {curly braces} for <label>
	$source = preg_replace_callback('|<label for="(.*)">|', 'labelVars', $source);

	$source = preg_replace('/{{1,2}err for="(.*)"\}{1,}?(.*){(calendar|checkbox|filefield|password|radio|selectfield|textfield|textarea)(.*)}(.*){\/err}/msU', '\\2<fieldset class="error">{\\3 name="\\1" \\4}\\5
	<div class="errorText hidden{error for="\\1"} visible{/error}">{error for="\\1"}{$msg}{/error}</div>
	</fieldset>', $source);

	// pass block as parameter for another block
	// for example, {maketext text=sometext params=|link user/login|}
	$source = replaceNonLiteral('/{([^\{\}\n]+)\{([^\{\}\n]+)\}(.*)}/', '{capture assign=blockAsParamValue}{$2}{/capture}{$1\$blockAsParamValue$3}', $source);

	// shorthand syntax for foreach
	// for example {foreach $items as $item}
	$source = preg_replace('/{foreach \$([^ ]+)[ ]+as[ ]+\$([^ ]+)}/', '{foreach from=\$$1 item=$2}', $source);

	// link shorthand syntax
	// {link user/login} is equal to {link controller=user action=login}
	$source = preg_replace('/{link ([a-zA-Z0-9]*)\/([a-zA-Z0-9]*)(.*)}/', '{link controller="$1" action="$2" $3}', $source);

	// translations
	$source = preg_replace('/{tn (.+?)}/', '{translate|escape:"html" text="$1" disableLiveTranslation="true"}', $source);
	$source = preg_replace('/{t ([^\|]+?)}/', '{translate text="$1"}', $source);
	$source = preg_replace('/{t ([^|]+)\|([^}]+)}/', '{capture assign="translation_$1"}{translate text=$1}{/capture}{\$translation_$1|$2}', $source);

	// roles
	$source = preg_replace('/{role ([\w.]+)}/', '{role name="$1"}', $source);

	// template customizations - allow to load from a different source
	$source = preg_replace('/{include file="([-_.a-zA-Z0-9@\/]+)"(.*)}/msU', '{include file="custom:$1"\\2}', $source);

	//$source = preg_replace('/{block (.+?)}/', '{foreach from=\$$1 item=includedBlock key=key}{$includedBlock}{/foreach}', $source);
	$source = preg_replace('/{block (.+?)}/', '{renderBlock block=$1}', $source);

	// help system
	$tipPattern = '([-_.a-zA-Z0-9@\/\$]+?)';
	$source = preg_replace('/{tip ' . $tipPattern . ' ' . $tipPattern . '}/', '{toolTip label=$1 hint=$2}', $source);
	$source = preg_replace('/{tip ' . $tipPattern . '}/', '{toolTip label=$1}', $source);
	$source = preg_replace('/{help (.+?)}/', '{helpLink id=$1}', $source);
	$source = preg_replace('/{see (.+?)}/', '{helpSeeAlsoItem id=$1}', $source);

	// remove {fetch} tags
	$source = preg_replace('/{fetch (.+?)}/', '', $source);

	return $source;
}

function replaceNonLiteral($from, $to, $what)
{
	$index = 0;
	$tagPos = 0;
	$inLiteral = false;
	$result = '';
	while ($index < strlen($what))
	{
		$nextTag = $inLiteral ? '{/literal}' : '{literal}';
		$tagPos = strpos($what, $nextTag, $index);
		if ($tagPos === false)
		{
			$tagPos = strlen($what);
		}

		$portion = substr($what, $index, $tagPos - $index);
		if (!$inLiteral)
		{
			$portion = preg_replace($from, $to, $portion);
		}

		$result .= $portion;

		$inLiteral = 1 - $inLiteral;
		$index = $tagPos;
	}

	return $result;
}

function labelVars($var)
{
	return preg_replace("/`(.*)`/", "{\\1}", $var[0]);
}

?>