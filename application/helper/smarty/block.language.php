<?php
/**
 * Display a tip block
 *
 * @package application.helper.smarty
 * @author Rinalds Uzkalns <rinalds@integry.net>
 *
 * @package application.helper.smarty
 */
function smarty_block_language($params, $content, LiveCartSmarty $smarty, &$repeat)
{
	if ($repeat)
	{
		$smarty->languageBlock = $smarty->getApplication()->getLanguageSetArray();		
		$smarty->assign('languageBlock', $smarty->languageBlock);
		$smarty->assign('lang', array_shift($smarty->languageBlock));
		$smarty->langHeadDisplayed = false;
	}
	else
	{		
		if (!trim($content))
		{
			$repeat = false;
			return false;
		}
		
		if ($smarty->languageBlock)
		{
			$repeat = true;
		}

		$contentLang = $smarty->get_template_vars('lang');
		$content = '<div class="languageFormContainer languageFormContainer_' . $contentLang['ID'] . '">' . $content . '</div>';

		if (!$smarty->langHeadDisplayed)
		{
			$content = $smarty->fetch('block/backend/langFormHead.tpl') . $content;
			$smarty->langHeadDisplayed = true;
		}

		$smarty->assign('lang', array_shift($smarty->languageBlock));

		// form footer
		if (!$repeat)
		{
			$content .= $smarty->fetch('block/backend/langFormFoot.tpl');
		}

		return $content;				
	}
}
?>