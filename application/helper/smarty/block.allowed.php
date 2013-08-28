<?php

/**
 * Display a tip block
 *
 * @package application.helper.smarty
 * @author Integry Systems
 *
 * @package application.helper.smarty
 */
function smarty_block_allowed($params, $content, Smarty_Internal_Template $smarty, &$repeat)
{
	if (!$repeat)
	{
				if(AccessStringParser::run($params['role']))
		{
			return $content;
		}
	}
}

?>