<?php

/**
 * Display page section header and footer only if content is present
 * This helps to avoid using redundant if's
 *
 * @param array $params
 * @param Smarty $smarty
 * @param $repeat
 *
 * <code>
 *	{sect}
 *		{header}
 *			Section header
 *		{/header}
 *		{content}
 *			Section content
 *		{/content}
 *		{footer}
 *			Section footer
 *		{/footer}
 *  {/sect}
 * </code>
 *
 * @return string Section HTML code
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_block_footer($params, $content, LiveCartSmarty $smarty, &$repeat)
{
	if (!$repeat)
	{
		$counter = $smarty->get_template_vars('sectCounter');
		$blocks = $smarty->get_template_vars('sect');
		$blocks[$counter]['footer'] = $content;
		$smarty->assign('sect', $blocks);
	}
}

?>