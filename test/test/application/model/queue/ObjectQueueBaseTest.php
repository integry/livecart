<?php
require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.queue.*');

/**
 *
 * @package test.model.queue
 * @author Shumoapp <http://shumoapp.com>
 */
class ObjectQueueBaseTest extends LiveCartTest
{
	/**
	 * @var
	 */
	private $queue;

	/**
	 * @return array
	 */
	public function getUsedSchemas()
	{
		return array(
			'MysqlQueue'
		);
	}

	/**
	 * Setup the queue and clear it in case it's not empty
	 */
	public function setUp()
	{
		parent::setUp();
		ActiveRecordModel::executeUpdate('DELETE FROM MysqlQueue');
		$this->getApplication()->getConfig()->set('QUEUE_METHOD', 'MysqlQueue');
		$this->queue = new ObjectQueueBase($this->getApplication()->getConfig(), 754754);

		//Empty the queue in case there was some leftovers.
		while($this->queue->receive()){};
	}

	public function testSendAndReceiveObjects()
	{
		$bar = new foo;

		$this->queue->send($bar, 1);
		$message = $this->queue->receive();

		$this->assertEquals($bar, $message);
	}

}

class foo
{
	public $pub = 'pub';
	private $priv = 'priv';
	protected $prot = 'prot';

	function do_foo()
	{
		echo "Doing foo.";
	}
}