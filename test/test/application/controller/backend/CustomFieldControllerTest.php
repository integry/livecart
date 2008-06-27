<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../../Initialize.php';

ClassLoader::import("application.controller.backend.CustomFieldController");

/**
 *
 * @package test.controller.backend
 * @author Integry Systems
 */
class CustomFieldControllerTest extends UnitTest implements ControllerTestCase
{
	private $controller;

	public function getUsedSchemas()
	{
		return array(
			'EavField',
		);
	}

	public function setUp()
	{
		parent::setUp();
		$this->controller = new CustomFieldController(self::getApplication());
	}

	public function testIndex()
	{
		$response = $this->controller->index();
		$this->assertIsA($response, 'ActionResponse');

		$nodes = $response->get('nodes');
		$this->assertEqual(count($nodes), count(EavField::getEavClasses()));
	}
}

?>