<?php

/**
 * Tab container
 *
 * @package application.helper.smarty
 * @author Integry Systems
 *
 * @package application.helper.smarty
 */
function smarty_block_tabControl($params, $content, LiveCartSmarty $smarty, &$repeat)
{
	if (!$repeat)
	{
		if (empty($params['noHidden']))
		{
			$more = '<li class="moreTabs">
						<a href="#" class="moreTabsLink"><span class="downArrow">&#9662; </span><span>' . strtolower($smarty->getApplication()->translate('_more_tabs')) . '</span></a>
						<div class="moreTabsMenu" style="display: none;"></div>
					</li>';
		}

		$content = '<ul id="' . $params['id'] . '" class="tabList tabs">' . $more . $content . '</ul>';

		$content .= '<script type="text/javascript">var tabCust = new TabCustomize($("' . $params['id'] . '")); tabCust.setPrefsSaveUrl("' . $smarty->getApplication()->getRouter()->createUrl(array('controller' => 'backend.index', 'action' => 'setUserPreference')) . '")</script>';

		return $content;
	}
}
?>