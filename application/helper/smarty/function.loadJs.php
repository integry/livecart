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
function smarty_function_loadJs($params, LiveCartSmarty $smarty) 
{
	$files = array("library/prototype/prototype.js", "frontend/Frontend.js");

	if (isset($params['form']))
	{
		$files[] = "library/scriptaculous/scriptaculous.js";
		$files[] = "library/form/Validator.js";
		$files[] = "library/form/ActiveForm.js";
	}
	
	include_once('function.includeJs.php');
	
	foreach ($files as $file)
	{
		smarty_function_includeJs(array('file' => $file), $smarty);	
	}
}

?>