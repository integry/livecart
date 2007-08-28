<?php

/**
 * Form field error message block
 *
 * @package application.helper.smarty.form
 * @author Saulius Rupainis <saulius@integry.net>
 */
function smarty_block_error($params, $content, $smarty, &$repeat) {
	$smarty->assign("msg", "");
	
	$formParams = $smarty->_tag_stack[0][1];
	$formHandler = $formParams['handle'];
	
	$validator = $formHandler->getValidator();
	$errorMsg = "";
	if (empty($params))
	{
		$errorList = $validator->getErrorList();
		if (empty($errorList))
		{
			$content = "";
		}
	}
	
	if (!empty($params['list']))
	{
		$smarty->assign($params['list'], $validator->getErrorList);
	}
	
	if (!empty($params['for']))
	{
		$msgVarName = "msg";
		$fieldName = $params['for'];
		$errorList = $validator->getErrorList();
		$errorMsg = "";
		
		if (!empty($params['msg']))
		{
			$msgVarName = $params['msg'];
		}
		if (!empty($errorList[$fieldName]))
		{
			$errorMsg = $errorList[$fieldName];
			$smarty->assign($msgVarName, $errorMsg);
		}
		else
		{
			$content = "";
			$smarty->assign($msgVarName, "");
		}
	}
	
	return $content;
}

?>