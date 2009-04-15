{form action="controller=order action=addToCart id=`$product.ID`" handle=$cartForm method="POST"}
	<table id="productPurchaseLinks">

	{blocks id="PRODUCT-PURCHASE-CONTAINER" blocks="
		PRODUCT-PRICE
		PRODUCT-TO-CART
		PRODUCT-ACTIONS"}

	</table>
{/form}