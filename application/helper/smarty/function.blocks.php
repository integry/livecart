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
function smarty_function_blocks($params, LiveCartSmarty $smarty)
{
	$app = $smarty->getApplication();

	$blocks = explode(' ', trim(preg_replace('/\s+/', ' ', $params['blocks'])));
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