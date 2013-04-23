<?php

include_once dirname(__file__) . '/function.includeCss.php';

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
function smarty_function_loadJs($params, Smarty_Internal_Template $smarty)
{
	include_once('function.includeJs.php');

	$files = array();

	$files[] = "library/jquery/jquery-min.js";
	$files[] = "library/jquery/plugins.js";
	$files[] = "library/prototype/prototype.js";
	$files[] = "library/livecart.js";
	$files[] = "library/FooterToolbar.js"; // need to be before Frontend.js
	$files[] = "frontend/Frontend.js";
	$files[] = "library/lightbox/lightbox.js";
	$files[] = "library/scriptaculous/scriptaculous.js";
	$files[] = "library/scriptaculous/builder.js";
	$files[] = "library/scriptaculous/dragdrop.js";
	$files[] = "library/scriptaculous/controls.js";
	$files[] = "library/scriptaculous/slider.js";
	$files[] = "library/scriptaculous/sound.js";

	if (isset($params['form']))
	{
		$files[] = "library/form/Validator.js";
		$files[] = "library/form/ActiveForm.js";
		$files[] = "library/form/State.js";
	}

	foreach ($files as $file)
	{
		smarty_function_includeJs(array('file' => $file), $smarty);
	}

	smarty_function_includeCss(array('file' => "library/lightbox/lightbox.css"), $smarty);
	smarty_function_includeCss(array('file' => "library/jquery/jquery-plugins.css"), $smarty);
}

?>
