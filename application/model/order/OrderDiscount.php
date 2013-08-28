<?php


/**
 * Represents an order level discount (discount that applies to the subtotal of the whole order)
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>
 */
class OrderDiscount extends ActiveRecordModel
{
	/**
	 * Define database schema used by this active record instance
	 *
	 * @param string $className Schema name
	 */



		public $ID;
		public $orderID", "CustomerOrder", "ID", "CustomerOrder;
		public $amount;
		public $description;
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(CustomerOrder $order)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->order = $order);
		return $instance;
	}

	public function save()
	{
		parent::save();
		$this->order->get()->registerFixedDiscount($this);
	}

	public function toArray()
	{
		$array = parent::toArray();
		$array['formatted_amount'] = $this->order->get()->currency->get()->getFormattedPrice($array['amount'] * -1);
		return $array;
	}
}

?>