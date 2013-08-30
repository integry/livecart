<?php

/**
 * Tab
 *
 * @package application/helper/smarty
 * @author Integry Systems
 *
 * @package application/helper/smarty
 */
function smarty_block_tab($params, $content, Smarty_Internal_Template $smarty, &$repeat)
{
	if (!$repeat)
	{
				if(!empty($params['role']) && !AccessStringParser::run($params['role']))
		{
			return false;
		}

		$user = $this->sessionUser->getUser();
		$userPref = $user->getPreference('tab_' . $params['id']);
		$isHidden = is_null($userPref) ? !empty($params['hidden']) : $userPref == 'false';

		$content = '
<li id="' . $params['id'] . '" rel="' . $params['help'] . '" class="tab ui-state-default ui-corner-top inactive' . ($isHidden ? ' hidden' : '') . '">' . $content . '</li>';

		return $content;
	}
}
?>