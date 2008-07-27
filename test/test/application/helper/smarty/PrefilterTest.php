<?php

if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.helper.smarty.prefilter#config");

/**
 *  @author Integry Systems
 *  @package test.helper.smarty
 */
class PrefilterTest extends UnitTest
{
	public function testBlockAsParamValue()
	{
		$code = '{someblock test={anotherblock}}';
		$replaced = smarty_prefilter_config($code, null);
		$this->assertEqual($replaced, '{capture assign=blockAsParamValue}{anotherblock}{/capture}{someblock test=$blockAsParamValue}');
	}

	public function testBlockAsParamValueInsideLiteral()
	{
		$code = '<script type="text/javascript">
	{literal}
		var emptyGroupModel = new Backend.RelatedProduct.Group.Model({Product: {ID: {/literal}{$productID}{literal}}}, Backend.availableLanguages);
		new Backend.RelatedProduct.Group.Controller($("productRelationshipGroup_new_{/literal}{$productID}{literal}_form").down(\'.productRelationshipGroup_form\'), emptyGroupModel);
	{/literal}
	</script>';
		$replaced = smarty_prefilter_config($code, null);

		$this->assertEqual($replaced, $code);
	}
}
?>
