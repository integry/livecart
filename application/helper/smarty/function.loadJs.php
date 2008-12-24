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
	include_once('function.includeJs.php');

	$files = array("library/prototype/prototype.js", "library/livecart.js", "frontend/Frontend.js");

	if (isset($params['form']) || true)
	{
		$files[] = "library/scriptaculous/scriptaculous.js";
		$files[] = "library/scriptaculous/builder.js";
		$files[] = "library/scriptaculous/effects.js";
		$files[] = "library/scriptaculous/dragdrop.js";
		$files[] = "library/scriptaculous/controls.js";
		$files[] = "library/scriptaculous/slider.js";
		$files[] = "library/scriptaculous/sound.js";
		$files[] = "library/form/Validator.js";
		$files[] = "library/form/ActiveForm.js";
	}

	foreach ($files as $file)
	{
		smarty_function_includeJs(array('file' => $file), $smarty);
	}
}

?>