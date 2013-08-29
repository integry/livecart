<?php

/**
 * ...
 *
 * @param array $params
 * @param Smarty $smarty
 * @return string
 *
 * @package application/helper/smarty
 * @author Integry Systems
 */
function smarty_function_json($params, Smarty_Internal_Template $smarty)
{
	$array = $params['array'];
	$assign = isset($params['assign']) ? $params['assign'] : false;

		$javaObject = @json_encode($array);

	if (!empty($params['escape']))
	{
		$javaObject = addslashes($javaObject);
	}

	if(!$assign)
	{
		return $javaObject;
	}
	else
	{
		$smarty->assign($assign, $javaObject);
	}
}

?>