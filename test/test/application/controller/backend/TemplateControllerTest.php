<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../../Initialize.php';

ClassLoader::import("application.controller.backend.TemplateController");

/**
 *
 * @package test.model.category
 * @author Integry Systems
 */
class TemplateControllerTest extends LiveCartTest implements BackendControllerTestCase
{
	/**
	 * Root category
	 * @var Category
	 */
	private $controller;

	public function setUp()
	{
		parent::setUp();
		$this->controller = new TemplateController(self::getApplication());
	}

	public function tearDown()
	{
		parent::tearDown();

		$dir = ClassLoader::getRealPath('storage.customize.view.theme.sometheme');
		if (file_exists($dir))
		{
			rmdir($dir);
		}
	}

	public function testSave()
	{
		$this->request->set('file', 'test.tpl');
		$this->request->set('code', 'test code');

		$response = $this->controller->save();

		$template = new Template('test.tpl');
		$this->assertEquals($template->getCode(), 'test code');
		$this->assertEquals($template->getFileName(), 'test.tpl');

		$template->restoreOriginal();
	}

	public function testThemeSave()
	{
		$this->request->set('file', 'test.tpl');
		$this->request->set('code', 'test code');
		$this->request->set('theme', 'sometheme');

		$response = $this->controller->save();

		$template = new Template('test.tpl', 'sometheme');
		$this->assertEquals($template->getCode(), 'test code');
		$this->assertEquals($template->getFileName(), 'theme/sometheme/test.tpl');

		$template->restoreOriginal();
	}

	public function testSaveCustomFile()
	{
		// create custom file first
		$this->request->set('file', 'theme/sometheme/test.tpl');
		$this->request->set('code', 'test code');
		$response = $this->controller->save();

		// edit the file
		$this->request->set('file', 'theme/sometheme/test.tpl');
		$this->request->set('code', 'test code');

		$response = $this->controller->save();

		$template = new Template('test.tpl', 'sometheme');
		$this->assertEquals($template->getCode(), 'test code');
		$this->assertEquals($template->getFileName(), 'theme/sometheme/test.tpl');

		$template->restoreOriginal();
	}
}

?>