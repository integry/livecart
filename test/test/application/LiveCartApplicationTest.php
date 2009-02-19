<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/Initialize.php';

ClassLoader::import('application.LiveCart');
ClassLoader::import('application.model.Currency');
ClassLoader::import('test.fixture.controller.PluginTestController');

/**
 *
 * @package test.application
 * @author Integry Systems
 */
class LiveCartApplicationTest extends LiveCartTest
{
	public function testControllerPlugins()
	{
		$app = $this->getApplication();

		// no plugin
		$controller = new PluginTestController($app);
		$response = $app->execute($controller, 'index');
		$this->assertFalse($response->get('success'));

		// with plugin
		$app->registerPluginDirectory(ClassLoader::getRealPath('test.fixture.plugin'));
		$app->loadPlugins();
		$controller = new PluginTestController($app);
		$response = $app->execute($controller, 'index');
		$this->assertTrue($response->get('success'));
		$this->assertTrue($controller->testValue);
		$this->assertTrue($controller->baseInitValue);

		$app->unregisterPluginDirectory(ClassLoader::getRealPath('test.fixture.plugin'));
	}

	public function testModelPlugins()
	{
		$this->getApplication()->registerPluginDirectory(ClassLoader::getRealPath('test.fixture.plugin'));

		$currency = Currency::getNewInstance('ZZZ');
		$currency->save();

		$this->assertEquals($currency->rate->get(), 0.5);
		$this->assertFalse((bool)$currency->isEnabled->get());

		$currency->rate->set(0.6);
		$currency->save();
		$this->assertTrue((bool)$currency->isEnabled->get());

		$array = $currency->toArray();
		$this->assertTrue($array['testValue']);

		$this->getApplication()->unregisterPluginDirectory(ClassLoader::getRealPath('test.fixture.plugin'));
	}

}

?>