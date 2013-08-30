<?php

/**
 * Language forms
 *
 * @package application/helper/smarty
 * @author Integry Systems
 *
 * @package application/helper/smarty
 */
function smarty_block_language($params, $content, Smarty_Internal_Template $smarty, &$repeat)
{
	//$smarty = $smarty->smarty;
	$app = $smarty->smarty->getApplication();

	if (!$app->getLanguageSetArray())
	{
		return false;
	}

	if ($repeat)
	{
		$app->languageBlock = $app->getLanguageSetArray();
		$smarty->assign('languageBlock', $app->languageBlock);
		$smarty->assign('lang', array_shift($app->languageBlock));
		$app->langHeadDisplayed = false;

		$user = $this->sessionUser->getUser();
		foreach ($app->getLanguageSetArray() as $lang)
		{
			$userPref = $user->getPreference('tab_lang_' . $lang['ID']);
			$isHidden = is_null($userPref) ? !empty($params['hidden']) : $userPref == 'false';
			$classNames[$lang['ID']] = $isHidden ? 'hidden' : '';
		}

		$app->langClassNames = $classNames;
	}
	else
	{
		if (!trim($content))
		{
			$repeat = false;
			return false;
		}

		if ($app->languageBlock)
		{
			$repeat = true;
		}

		$contentLang = $smarty->getTemplateVars('lang');
		$content = '<tab class="lang_' . $contentLang['ID'] . '" heading="' . $contentLang['originalName'] . '">' . $content . '</tab>';

		if (!$app->langHeadDisplayed)
		{
			$smarty->assign('classNames', $app->langClassNames);
			$content = $smarty->fetch('block/backend/langFormHead.tpl') . $content;
			$app->langHeadDisplayed = true;
		}

		$smarty->assign('lang', array_shift($app->languageBlock));

		// form footer
		if (!$repeat)
		{
			$content .= $smarty->fetch('block/backend/langFormFoot.tpl');
		}

		return $content;
	}
}
?>