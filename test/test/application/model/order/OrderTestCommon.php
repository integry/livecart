<?

if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';


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