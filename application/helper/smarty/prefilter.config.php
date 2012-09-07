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
	// replace `backticks` to {curly braces} for <label>
	$source = preg_replace_callback('|<label for="(.*)">|', 'labelVars', $source);

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

	// static content URLs
	$source = preg_replace('/{s (.+?)}/', '{static url="$1"}', $source);

	// roles
	$source = preg_replace('/{role ([\w.]+)}/', '{role name="$1"}', $source);

	// template customizations - allow to load from a different source
	$source = preg_replace('/{include file="([-_.a-zA-Z0-9@\/]+)"(.*)}/msU', '{include file="custom:$1"\\2}', $source);

	//$source = preg_replace('/{block (.+?)}/', '{foreach from=\$$1 item=includedBlock key=key}{$includedBlock}{/foreach}', $source);
	$source = preg_replace('/{block (.+?)}/', '{renderBlock block="$1"}', $source);

	// help system
	$tipPattern = '([\-_\.a-zA-Z0-9@\/\$]+?)';
	$source = preg_replace('/{tip ' . $tipPattern . ' ' . $tipPattern . '}/', '{toolTip label="$1" hint="$2"}', $source);
	$source = preg_replace('/{tip ' . $tipPattern . '}/', '{toolTip label="$1"}', $source);
	$source = preg_replace('/{help (.+?)}/', '{helpLink id="$1"}', $source);
	$source = preg_replace('/{see (.+?)}/', '{helpSeeAlsoItem id="$1"}', $source);

	// sections
	$source = str_replace('{head}', '{header}', $source);
	$source = str_replace('{cont}', '{/header}{content}', $source);
	$source = preg_replace('/{foot}(.*){\/sect}/msU', '{/content}{footer}$1{/footer}{/sect}', $source);

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