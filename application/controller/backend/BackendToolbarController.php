<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');
ClassLoader::import('application.model.system.BackendToolbarItem');
ClassLoader::import('application.model.user.User');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.order.CustomerOrder');

/**
 *
 * @author Integry Systems
 */

class BackendToolbarController extends StoreManagementController
{
	public function lastViewed()
	{
		$response = new ActionResponse();
		$response->set('randomToken', substr(md5(time().mt_rand(1,9999999999)),0,8));
		$response->set('lastViewed', 
			BackendToolbarItem::sanitizeItemArray(
				BackendToolbarItem::getUserToolbarItems(array(BackendToolbarItem::TYPE_PRODUCT, BackendToolbarItem::TYPE_USER, BackendToolbarItem::TYPE_ORDER),null, 'DESC')
			)
		);
		return $response;
	}

	public function addIcon()
	{
		$request = $this->getRequest();
		$menuID = $request->get('id');
		$position = $request->get('position');
		$items = BackendToolbarItem::getUserToolbarItems(BackendToolbarItem::TYPE_MENU);
		$itemArray = array();
		$previousPosition = -1;
		foreach($items as $item)
		{
			if ($position > $previousPosition && $position <= $item['position'])
			{
				$itemArray[] = array('menuID'=>$menuID);
				$position = -2;
			}
			$itemArray[] = $item;
			$previousPosition = $item['position'];
		}
		
		if ($position != -2)
		{
			$itemArray[] = array('menuID'=>$menuID);
		}
		
		if (BackendToolbarItem::saveItemArray($itemArray))
		{
			return new JSONResponse(null, 'success', $this->translate('_button_added'));
		}
		else
		{
			return new JSONResponse(null, 'failure', $this->translate('_cant_add_button'));
		}
	}

	public function removeIcon()
	{
		$request = $this->getRequest();

		if (BackendToolbarItem::deleteMenuItem($request->get('id'), $request->get('position')))
		{
			$this->fixSortOrder();
			return new JSONResponse(null, 'success', $this->translate('_button_removed'));
		}
		else
		{
			return new JSONResponse(null, 'failure', $this->translate('_cant_remove_button'));
		}
	}

	public function sortIcons()
	{
		$order = $this->getRequest()->get('order');
		$order = explode(',',$order);
		$itemArray = array();
		$items = BackendToolbarItem::getUserToolbarItems(BackendToolbarItem::TYPE_MENU);
		foreach($order as $menuID)
		{
			$c = count($items);
			for ($i=0; $i<=$c; $i++)
			{
				if ($items[$i]['menuID'] == $menuID)
				{
					$itemArray[] = $items[$i];
					unset($items[$i]);
					$items = array_values($items);
					break;
				}
			}
		}
		if (BackendToolbarItem::saveItemArray($itemArray))
		{
			return new JSONResponse(null, 'success', $this->translate('_button_order_changed'));
		}
		else
		{
			return new JSONResponse(null, 'failure', $this->translate('_cant_change_button_order'));
		}
	}

	private function fixSortOrder()
	{
		BackendToolbarItem::saveItemArray(BackendToolbarItem::getUserToolbarItems(BackendToolbarItem::TYPE_MENU));
	}
}

?>