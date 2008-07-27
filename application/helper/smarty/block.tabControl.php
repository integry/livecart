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
		$more = '<li class="moreTabs">
					<a href="#">' . $smarty->getApplication()->translate('_more_tabs') . ' &#9662;</a>
					<div class="moreTabsMenu" style="display: none;"></div>
				</li>';

		$content = '<ul id="' . $params['id'] . '" class="tabList tabs">' . $content . $more . '</ul>';

		$content .= '<script type="text/javascript">var tabCust = new TabCustomize($("' . $params['id'] . '")); tabCust.setPrefsSaveUrl("' . $smarty->getApplication()->getRouter()->createUrl(array('controller' => 'backend.index', 'action' => 'setUserPreference')) . '")</script>';

		return $content;
	}
}
?>