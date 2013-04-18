{form action="controller=order action=addToCart id=`$product.ID`" handle=$cartForm method="POST"}
	{blocks id="PRODUCT-PURCHASE-CONTAINER" blocks="
		PRODUCT-PRICE  		// product/block/price.tpl
		PRODUCT-RECURRING
		PRODUCT-UP-SELL
		PRODUCT-TO-CART"}
{/form}
