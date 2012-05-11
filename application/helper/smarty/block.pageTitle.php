<?php

/**
 * Set page title
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_block_pageTitle($params, $content, Smarty_Internal_Template $smarty, &$repeat)
{
	if (!$repeat)
	{
		$smarty->assign('TITLE', strip_tags($content));

		if (isset($params['help']))
		{
			$content .= '<script type="text/javascript">Backend.setHelpContext("' . $params['help'] . '")</script>';
		}
		$GLOBALS['PAGE_TITLE'] = $content;

		$smarty->assign('PAGE_TITLE', $content);
		$smarty->setGlobal('PAGE_TITLE', $content);
	}
}

?>