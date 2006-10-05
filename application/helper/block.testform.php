<?php

function smarty_block_testform($params, $content, $smarty, &$repeat) {
	
	$formObj = $params['instance'];
	$itemName = $params['item'];
	
	if (!($formObj instanceof Form)) {
		throw new HelperException("Template variable 'form' must be an instance of Form class");
	}
	unset($params['instance']);
	unset($params['item']);
	
	foreach ($params as $name => $value) {
		$form->setAttribute($name, $value);
	}
	
	$iterator = $smarty->get_template_vars("iterator");
	if (empty($iterator)) {
		$smarty->assign("iterator", $formObj->getIterator());
	}
	
	//$iterator = $formObj->getIterator();
	$currentSection = $iterator->current();
	$iterator->next();
	if ($iterator->current() != null) {
		$repeat = true;
	} else {
		$repeat = false;
	}
	
	$sectionArray = array("caption" => $currentSection->getCaption());
	$smarty->assign($itemName, $sectionArray);

	
	return $content;
}

?>