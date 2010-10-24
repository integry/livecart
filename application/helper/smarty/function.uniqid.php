<?php

/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application.helper.smarty
 * @author Integry Systems
 */
function smarty_function_uniqid($params, LiveCartSmarty $smarty)
{
	if (isset($params['last']))
	{
		return $smarty->get_template_vars('lastUniqId');
	}
	else
	{
		// start with a letter for XHTML id attribute value compatibility
		$id = 'a' . uniqid();
		$smarty->assign('lastUniqId', $id);

		if (isset($params['assign']))
		{
			$smarty->assign($params['assign'], $id);
			return $id;
		}

		return $id;
	}
}

?>