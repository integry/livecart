<?php

include_once dirname(dirname(__file__)) . '/TrackingMethod.php';

class GoogleAnalytics extends TrackingMethod
{
	public function getHtml()
	{
		$transHtml = array();

		$request = $this->controller->getRequest();
		if ($this->controller instanceof CheckoutController && ($request->getActionName() == 'completed'))
		{
			$session = new Session();
			if ($orderID = $session->get('completedOrderID'))
			{
				$order = CustomerOrder::getInstanceByID((int)$session->get('completedOrderID'), CustomerOrder::LOAD_DATA);
				$order->loadAll();
				$orderArray = $order->toArray();

				$data = array($order->getID(), '' /* affiliation? */, $orderArray['total'][$orderArray['Currency']['ID']], 0 /* tax */, $orderArray['ShippingAddress']['city'],  $orderArray['ShippingAddress']['stateName'],  $orderArray['ShippingAddress']['countryID']);
				$transHtml[] = 'pageTracker._addTrans' . $this->getJSParams($data);

				foreach ($orderArray['cartItems'] as $item)
				{
					$data = array($order->getID(), $item['Product']['sku'], $item['Product']['name'], $item['Product']['Category']['name'], $item['price'], $item['count']);
					$transHtml[] = 'pageTracker._addItem' . $this->getJSParams($data);
				}
			}

			$transHtml[] = 'pageTracker._trackTrans();';
		}

		return
'<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("' . $this->getValue('code') . '");
pageTracker._initData();
pageTracker._trackPageview();
' . implode("\n", $transHtml) . '
</script>';
	}

	private function getJSParams($array)
	{
		return '(' . substr(json_encode($array), 1, -1) . ');';
	}
}

?>