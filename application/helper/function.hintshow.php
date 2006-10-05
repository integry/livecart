<?php

/**
 * Helper for showing hints {@see http://javascript.geniusbug.com} For more information
 * Used together with {@see smarty_function_hinthide}
 * <code>
 *   ...
 * 		<a href="localhost" onmouseover="{hintshow content="Sample hint" moveWithMouse=1}"
 			onmouseout="{hinthide}"
		 >
 * </code> 
 *
 * @param array $params Array keys:
 * 		"content" => hint content
 *		"bgcolor" => background color of hint
 * 		"moveWithMouse" => if this key is set, than hint is moved together with mouse 
 * @param Smarty $smarty
 * @return string
 * 
 * @package application.helper
 */
function smarty_function_hintshow($params, $smarty) {

	$js = $smarty->get_template_vars('JAVASCRIPT');

	//it's enought just once check
	if (empty($js) || !in_array(Router::getInstance()->getBaseDir()."/public/javascript/hint/browser.js", $js)) {
		
		$smarty->append("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/hint/browser.js");
		$smarty->append("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/hint/basicdivobject.js");
		$smarty->append("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/hint/hint.js");	  	
		//activate javascript hints
		$smarty->append("BODY_ONLOAD", "activateHints();");		
	}
		
	if (isSet($params["moveWithMouse"])) {
	  
	  	return "movingHint.show('".$params["content"]."', '".$params["bgcolor"]."');";	  	
	} else {
	  	
		return "simpleHint.show('".$params["content"]."', '".$params["bgcolor"]."');";
	}
}

?>