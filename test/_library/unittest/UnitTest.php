<?php

include dirname(__file__) . '/ControllerTestCase.php';

abstract class UnitTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Creole database connection wrapper
	 *
	 * @var Connection
	 */
	private $db = null;

	/**
	 * List of tables which changes their autoincrement value
	 *
	 * @var unknown_type
	 */
	private $autoincrements = array();

	private $originalRequest;

	protected $config;

	private static $application;

	public function __construct($name = null)
	{
		parent::__construct($name);

		if (class_exists('ActiveRecord'))
		{
			$this->db = ActiveRecord::getDBConnection();
			ActiveRecordModel::beginTransaction();
		}

		$app = ActiveRecordModel::getApplication();
		$this->originalRequest = clone $app->getRequest();
		$this->config = $app->getConfig();
		$app->getConfigContainer()->disableModules();
	}

	protected function getUsedSchemas()
	{
		return array();
	}

	public static function setApplication(Application $app)
	{
		self::$application = $app;
	}

	public static function getApplication()
	{
		return self::$application;
	}

	public function assertEqual($a, $b)
	{
		return $this->assertEquals($a, $b);
	}

	public function pass()
	{
		$this->assertTrue(true);
	}

	public function assertIsA($object, $type)
	{
		return $this->assertType($type, $object);
	}

	public function setUp()
	{
		ActiveRecordModel::getApplication()->clearCachedVars();

		ActiveRecordModel::beginTransaction();
		if(empty($this->autoincrements))
		{
			foreach($this->getUsedSchemas() as $table)
			{
				$res = $this->db->executeQuery("SHOW TABLE STATUS LIKE '$table'");
				$res->next();
				$this->autoincrements[$table] = (int)$res->getInt("Auto_increment");
			}
		}

		if ($this instanceof BackendControllerTestCase)
		{

			// set up user
			$group = UserGroup::getNewInstance('Unit tester');
			$group->save();
			$group->setAllRoles();
			$group->save();
			$user = User::getNewInstance('unittest@test.com', null, $group);
			$user->save();
			SessionUser::setUser($user);
		}

		if ($this instanceof ControllerTestCase)
		{
			$this->request = self::getApplication()->getRequest();
		}
	}

	public function tearDown()
	{
		ActiveRecordModel::rollback();

		foreach($this->getUsedSchemas() as $table)
		{
			ActiveRecord::removeClassFromPool($table);
			$this->db->executeUpdate("ALTER TABLE $table AUTO_INCREMENT=" . $this->autoincrements[$table]);
		}

		self::getApplication()->setRequest(clone $this->originalRequest);
	}
}

function unitTestRollBack()
{
	ActiveRecordModel::rollback();
}

register_shutdown_function('unitTestRollBack');

?>