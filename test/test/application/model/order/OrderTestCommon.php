<?

if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.delivery.*");
ClassLoader::import("application.model.product.*");
ClassLoader::import("application.model.discount.*");
ClassLoader::import("application.model.order.*");
ClassLoader::import("application.model.user.User");
ClassLoader::import("application.model.user.*");
ClassLoader::import("application.model.Currency");
ClassLoader::import("application.model.tax.*");
ClassLoader::import("application.model.businessrule.BusinessRuleController");
ClassLoader::import("library.payment.*");

/**
 *
 *
 *  @author Integry Systems
 *  @package test.model.order
 */
abstract class OrderTestCommon extends LiveCartTest
{
	protected $order;

	protected $products = array();

	protected $usd;

	protected $user;

	public function setUp()
	{
		parent::setUp();

		ActiveRecordModel::executeUpdate('DELETE FROM Tax');
		ActiveRecordModel::executeUpdate('DELETE FROM TaxRate');
		ActiveRecordModel::executeUpdate('DELETE FROM Currency');
		ActiveRecordModel::executeUpdate('DELETE FROM DiscountCondition');
		ActiveRecordModel::executeUpdate('DELETE FROM DiscountAction');
		ActiveRecordModel::executeUpdate('DELETE FROM DeliveryZone');
		ActiveRecordModel::executeUpdate('DELETE FROM ShippingService');

		BusinessRuleController::clearCache();

		$this->initOrder();

		$this->config->setRuntime('DELIVERY_TAX', '');
	}

	public function getUsedSchemas()
	{
		return array(
			'CustomerOrder',
			'OrderedItem',
			'Shipment',
			'DiscountAction',
			'DiscountCondition',
			'DiscountConditionRecord',
			'DeliveryZone',
			'Tax',
			'User',
		);
	}
}

?>