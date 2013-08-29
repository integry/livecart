<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/Initialize.php';


/**
 *
 * @package test.application
 * @author Integry Systems
 */
class LiveCartApplicationTest extends LiveCartTest
{
	public function ztestControllerPlugins()
	{
		$app = $this->getApplication();

		// no plugin
		$controller = new PluginTestController($app);
		$response = $app->execute($controller, 'index');
		$this->assertFalse($response->get('success'));

		// with plugin
		$app->registerPluginDirectory($this->config->getPath('test.fixture.plugin'));
		$app->loadPlugins();
		$controller = new PluginTestController($app);
		$response = $app->execute($controller, 'index');
		$this->assertTrue($response->get('success'));
		$this->assertTrue($controller->testValue);
		$this->assertTrue($controller->baseInitValue);

		$app->unregisterPluginDirectory($this->config->getPath('test.fixture.plugin'));
	}

	public function ztestModelPlugins()
	{
		$this->getApplication()->registerPluginDirectory($this->config->getPath('test.fixture.plugin'));

		$currency = Currency::getNewInstance('ZZZ');
		$currency->save();

		$this->assertEquals($currency->rate->get(), 0.5);
		$this->assertFalse((bool)$currency->isEnabled->get());

		$currency->rate->set(0.6);
		$currency->save();
		$this->assertTrue((bool)$currency->isEnabled->get());

		$array = $currency->toArray();
		$this->assertTrue($array['testValue']);

		$this->getApplication()->unregisterPluginDirectory($this->config->getPath('test.fixture.plugin'));
	}

}

?>