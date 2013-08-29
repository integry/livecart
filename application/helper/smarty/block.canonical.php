<?php

/**
 * Set canonical URL
 *
 * @package application/helper/smarty
 * @author Integry Systems
 * @see http://googlewebmastercentral.blogspot.com/2009/02/specify-your-canonical.html
 */
function smarty_block_canonical($params, $content, Smarty_Internal_Template $smarty, &$repeat)
{
	if (!$repeat)
	{
		$parsed = parse_url($content);
		if (!empty($parsed['query']))
		{
			$pairs = array();
			foreach (explode('&amp;', $parsed['query']) as $pair)
			{
				$values = explode('=', $pair, 2);
				if (count($values) != 2)
				{
					continue;
				}

				$pairs[$value[0]] = $value[1];
			}

			$pairs = array_diff_key($pairs, array_flip(array('currency', 'sort')));
			$parsed['query'] = http_build_query($pairs);
		}

		$content = $parsed['path'] . (!empty($parsed['query']) ? '?' . $parsed['query'] : '');

		$GLOBALS['CANONICAL'] = $content;
		$smarty->assign('CANONICAL', $content);
		$smarty->setGlobal('CANONICAL', $content);
	}
}

?>