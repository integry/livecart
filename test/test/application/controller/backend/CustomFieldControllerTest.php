<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../../Initialize.php';


/**
 *
 * @package test.controller.backend
 * @author Integry Systems
 */
class CustomFieldControllerTest extends LiveCartTest implements BackendControllerTestCase
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

		// +1 for offline methods
		$this->assertEqual(count($nodes), count(EavField::getEavClasses()) + 1);
	}
}

?>