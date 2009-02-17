<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.template.Template');

/**
 * Test Template class
 *
 * @author Integry Systems
 * @package test.model.template
 */
class TemplateTest extends LiveCartTest
{
	function testRegularTemplate()
	{
		$template = new Template('somefile.tpl');
		$this->assertEquals($template->getFileName(), 'somefile.tpl');
	}

	function testThemeTemplate()
	{
		$template = new Template('somefile.tpl', 'sometheme');
		$this->assertEquals($template->getFileName(), 'theme/sometheme/somefile.tpl');
	}

	function testThemeTemplateToAnotherTheme()
	{
		$template = new Template('theme/default/layout/frontend.tpl', 'sometheme');
		$this->assertEquals($template->getFileName(), 'theme/sometheme/layout/frontend.tpl');
	}
}

?>