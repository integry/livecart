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
function smarty_block_sect($params, $content, LiveCartSmarty $smarty, &$repeat)
{
	if ($repeat)
	{
		$smarty->assign('sect', array('header' => '', 'content' => '', 'footer' => ''));
	}
	else
	{
		$blocks = $smarty->get_template_vars('sect');

		if (trim($blocks['content']))
		{
			return $blocks['header'] . $blocks['content'] . $blocks['footer'];
		}
	}

}

?>