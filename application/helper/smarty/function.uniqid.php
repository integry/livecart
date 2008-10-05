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
		$id = uniqid();
		$smarty->assign('lastUniqId', $id);
		return $id;
	}
}

?>