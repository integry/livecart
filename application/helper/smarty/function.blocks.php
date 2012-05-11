<?php

/**
 * Renders and displays a page content block
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_blocks($params, Smarty_Internal_Template $smarty)
{
	$app = $smarty->getApplication();

	$blocks = explode(' ', trim(preg_replace('/\s+/', ' ', $params['blocks'])));

	foreach ($blocks as $key => $value)
	{
		if (substr($value, 0, 2) == '//')
		{
			unset($blocks[$key]);
		}
	}

	$blocks = $app->getRenderer()->sortBlocks($blocks);

	$out = array();
	foreach ($blocks as $block)
	{
		$out[$block] = $app->getBlockContent($block);
	}
	$content = implode("\n", $out);

	if (!empty($params['id']))
	{
		$smarty->set('CONTENT', $content);
		$parent = $app->getBlockContent($params['id']);
		if ($parent)
		{
			$content = $parent;
		}
	}

	return $content;
}

?>