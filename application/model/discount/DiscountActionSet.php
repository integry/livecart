<?php

ClassLoader::import('library.activerecord.ARSet');
ClassLoader::import('application.model.discount.DiscountAction');

/**
 *
 * @package application.model.discount
 * @author Integry Systems <http://integry.com>
 */
class DiscountActionSet extends ARSet
{
	public function getActionsByType($type)
	{
		$result = new DiscountActionSet();
		foreach ($this as $rec)
		{
			if ($rec->actionType == $type)
			{
				$result->add($rec);
			}
		}

		return $result;
	}
}

?>