<?php

function smarty_function_langform($params, $smarty) {
	
	$formObj = $params['form'];
	if (!($formObj instanceof LangForm)) {
		throw new HelperException("Template variable 'form' must be an instance of LangForm");
	}
	
	$formHTML = "";
	$formHTML .= $formObj->renderHeader();
	
	foreach ($formObj as $section) {
		$formHTML .= "\t<fieldset>\n";
		$formHTML .= "\n<legend>\n" . $section->getCaption() . "</legend>";
		$formHTML .= "<table>";
		foreach ($section as $field) {
			
			$formHTML .= "<tr$rowStyle>";
			$formHTML .= "<td>" . $field->getCaption() . "</td><td>" . $field->render() . "</td>";
			$formHTML .= "</tr>";
		}
		$formHTML .= "</table>\n";
		$formHTML .= "\t</fieldset>\n";
	}
	
	
	// Multilingual form fields
	$formHTML .= "<fieldset>";
	$formHTML .= '<legend>Enter Product Information in Other Languages</legend>' . "\n";
	foreach ($formObj->getLanguageList() as $lang) {
		$formHTML .= "\t<fieldset>";
		$formHTML .= "\t<legend> <a href=\"\">+</a>  Lang " . $lang['ID'] . "</legend>\n";
		$formHTML .= "\t<table>\n";
		foreach ($formObj as $section) {
			foreach ($section as $field) {
				if ($field->getAttribute('lang')) {
					// found a multilingual form field
					$formHTML .= "<tr><td>" . $field->getCaption() . "</td><td>" . $field->render() . "</td></tr>";
				}
			}
		}
		$formHTML .= "\t</table>\n";
		$formHTML .= "\t</fieldset>\n";
	}
	$formHTML .= "</fieldset>\n";
	
	$formHTML .= $formObj->renderFooter();
	
	return $formHTML;
}

?>