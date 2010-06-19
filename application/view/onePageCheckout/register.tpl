{form action="controller=onePageCheckout action=setShippingAddress" method="POST" handle=$form}
	{include file="user/block/registerAddress.tpl" prefix="shipping_"}
{/form}
