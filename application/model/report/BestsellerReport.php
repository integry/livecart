<?php


/**
 * Generate list of best selling products
 *
 * @package application/model/report
 * @author	Integry Systems
 */
class BestsellerReport extends Report
{
	protected function getMainTable()
	{
		return 'OrderedItem';
	}

	protected function getDateHandle()
	{
		return new ARFieldHandle('CustomerOrder', 'dateCompleted');
	}

	public function getBestsellersByCount()
	{
		return $this->getBestsellerData('SUM(OrderedItem.count)');
	}

	public function getBestsellersByTotal()
	{
		return $this->getBestsellerData('ROUND(SUM(OrderedItem.count * (OrderedItem.price * ' . $this->getCurrencyMultiplier() . ')), 2)');
	}

	private function getBestsellerData($sql, $order = 'DESC')
	{
		$this->setChartType(self::TABLE);
		$q = $this->getQuery($sql);

		$f = $q->getFilter();
		$f->reorder();
		$f->resetGrouping();
		$f->order(new ARExpressionHandle('cnt'), $order);
		$q->addField('OrderedItem.productID');
		$f->setGrouping(new ARExpressionHandle('OrderedItem.productID'));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), 1));
		$f->limit(self::TABLE_LIMIT);
		$q->joinTable('CustomerOrder', 'OrderedItem', 'ID', 'customerOrderID');

		$this->getReportData($q);

		$ids = array();
		foreach ($this->values as $product)
		{
			$ids[$product['productID']] = $product['cnt'];
		}

		// fetch product details
		$fields = array_flip(array('sku', 'name', 'cnt'));
		$products = ActiveRecordModel::getRecordSetArray('Product', new ARSelectFilter(new INCond(new ARFieldHandle('Product', 'ID'), array_keys($ids))), array('Parent'));
		ProductSet::loadVariationsForProductArray($products);

		foreach ($products as $product)
		{
			$product['cnt'] = $ids[$product['ID']];
			if (empty($product['name']))
			{
				if (!empty($product['parentID']))
				{
					$parent = Product::getInstanceByID($product['parentID'], true);
					$product['name'] = $parent->getValueByLang('name');
				}
				else
				{
					$product['name'] = '';
				}
			}

			if (isset($product['variationValues']))
			{
				$product['name'] .= ' (' . implode(' / ', $product['variationValues']) . ')';
			}

			// array_merge to put all array values in the same order
			$ids[$product['ID']] = array_merge($fields, array_intersect_key($product, $fields));
		}

		$this->values = $ids;
	}

	public function getCartCounts()
	{
		$this->getData('COUNT(*)');
	}
}

?>